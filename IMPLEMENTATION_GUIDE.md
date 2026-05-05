# Performance Improvements: Implementation Guide

Quick reference for implementing the performance recommendations.

---

## Phase 1: Database Indexes (High Priority)

### Step 1: Create Migration

```bash
php artisan make:migration add_performance_indexes
```

**File: `database/migrations/2026_05_06_000001_add_performance_indexes.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            // Composite index for list filtering
            $table->index(['status', 'availability', 'created_at']);
            
            // Index for species lookups on published animals
            $table->index(['species_id'], name: 'idx_animals_species_id')
                  ->where('status', 'published');
        });

        Schema::table('classifieds', function (Blueprint $table) {
            // Composite index for list filtering and sorting
            $table->index(['status', 'price', 'created_at']);
        });

        Schema::table('media', function (Blueprint $table) {
            // Partial index for approved media lookups
            $table->rawIndex(
                'mediable_type, mediable_id, moderation_status',
                'idx_media_approved'
            )->where('moderation_status', '=', 'approved');
        });

        // Text search indexes (PostgreSQL GIN)
        Schema::table('animals', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('CREATE INDEX idx_animals_pet_name_gin ON animals USING gin(pet_name gin_trgm_ops)');
                DB::statement('CREATE INDEX idx_animals_description_gin ON animals USING gin(description gin_trgm_ops)');
            }
        });

        Schema::table('species', function (Blueprint $table) {
            $table->index(['species_number']);
            $table->index(['higher_taxa']);
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropIndex(['status', 'availability', 'created_at']);
            $table->dropIndex('idx_animals_species_id');
        });

        Schema::table('classifieds', function (Blueprint $table) {
            $table->dropIndex(['status', 'price', 'created_at']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('idx_media_approved');
        });

        Schema::table('species', function (Blueprint $table) {
            $table->dropIndex(['species_number']);
            $table->dropIndex(['higher_taxa']);
        });

        // Drop GIN indexes if using PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_animals_pet_name_gin');
            DB::statement('DROP INDEX IF EXISTS idx_animals_description_gin');
        }
    }
};
```

### Step 2: Run Migration

```bash
php artisan migrate

# Verify indexes were created
php artisan tinker
>>> DB::select('SELECT * FROM pg_stat_user_indexes WHERE tablename = \'animals\'');
```

---

## Phase 2: Eager Loading (High Priority)

### Step 1: Update Animal Controller

**File: `app/Http/Controllers/AnimalController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Enums\AnimalAvailability;
use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        $sort         = $request->query('sort', 'recent');
        $search       = $request->query('search');
        $availability = $request->query('availability');

        $query = Animal::query()
            ->where('status', 'published')
            // ✅ EAGER LOAD: Reduces N+1 queries
            ->with([
                'media' => fn($q) => $q
                    ->where('moderation_status', 'approved')
                    ->orderBy('id', 'desc')
                    ->limit(1),  // Only first approved image per animal
                'species' => fn($q) => $q->select('id', 'species', 'common_name'),
                'user' => fn($q) => $q->select('id', 'name'),
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Consider using ILIKE for PostgreSQL if GIN indexes added
                $q->where('pet_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($availability && AnimalAvailability::tryFrom($availability)) {
            $query->where('availability', $availability);
        }

        match ($sort) {
            'name-asc'  => $query->orderBy('pet_name', 'asc'),
            'name-desc' => $query->orderBy('pet_name', 'desc'),
            'oldest'    => $query->oldest('created_at'),
            default     => $query->latest('created_at'),
        };

        return view('animals.index', [
            'animals'        => $query->paginate(24)->withQueryString(),
            'currentSort'    => $sort,
            'search'         => $search,
            'availability'   => $availability,
            'availabilities' => AnimalAvailability::cases(),
        ]);
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        // ✅ EAGER LOAD: Load all necessary relationships
        return view('animals.show', [
            'animal' => $animal->load([
                'media' => fn($q) => $q
                    ->where('moderation_status', 'approved')
                    ->orderBy('id', 'desc'),
                'species' => fn($q) => $q->select('id', 'species', 'common_name', 'higher_taxa', 'author'),
                'user' => fn($q) => $q->select('id', 'name', 'email'),
            ])
        ]);
    }
}
```

### Step 2: Update Classified Controller

**File: `app/Http/Controllers/ClassifiedController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'recent');
        $minPrice = $request->query('min_price', null);
        $maxPrice = $request->query('max_price', null);
        $search = $request->query('search', null);

        $query = Classified::where('status', 'published')
            // ✅ EAGER LOAD: Reduces N+1 queries
            ->with([
                'media' => fn($q) => $q
                    ->where('moderation_status', 'approved')
                    ->orderBy('id', 'desc')
                    ->limit(1),
                'user' => fn($q) => $q->select('id', 'name', 'email'),
            ]);

        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        match ($sort) {
            'price-low'  => $query->orderBy('price', 'asc'),
            'price-high' => $query->orderBy('price', 'desc'),
            'oldest'     => $query->oldest('created_at'),
            default      => $query->latest('created_at'),
        };

        return view('classifieds.index', [
            'classifieds' => $query->paginate(12),
            'currentSort' => $sort,
            'minPrice'    => $minPrice,
            'maxPrice'    => $maxPrice,
            'search'      => $search,
        ]);
    }

    public function show(Classified $classified)
    {
        $this->authorize('view', $classified);

        // ✅ EAGER LOAD
        return view('classifieds.show', [
            'classified' => $classified->load([
                'media' => fn($q) => $q
                    ->where('moderation_status', 'approved')
                    ->orderBy('id', 'desc'),
                'user' => fn($q) => $q->select('id', 'name', 'email'),
            ])
        ]);
    }
}
```

### Step 3: Add Query Scopes to Media Model

**File: `app/Models/Media.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'url', 'user_id', 'license', 'license_url', 'source_url', 'copyright', 'author', 'title', 'moderation_status',
    ];

    // ... existing code ...

    /**
     * Scope: Get approved media only
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Scope: Get first approved thumbnail
     */
    public function scopeApprovedThumbnail(Builder $query): Builder
    {
        return $query->where('moderation_status', 'approved')
                     ->orderBy('id', 'desc')
                     ->limit(1);
    }

    /**
     * Scope: Get pending media for moderation
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('moderation_status', 'pending');
    }
}
```

### Step 4: Test N+1 Elimination

```bash
php artisan tinker

# Check query count for /animals page (should be ~6 queries)
>>> DB::enableQueryLog();
>>> Animal::where('status', 'published')->with(['media', 'species', 'user'])->paginate(24);
>>> count(DB::getQueryLog());

# Should return ~6, not 52
```

---

## Phase 3: Caching Service (Medium Priority)

### Step 1: Create Inventory Service

**File: `app/Services/AnimalInventoryService.php`**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AnimalInventoryService
{
    private const CACHE_TTL = 30 * 60;  // 30 minutes
    private const FILE_PATH = 'app/public/animals.json';

    /**
     * Get animals with applied sorting
     */
    public function getAnimals(string $sort = 'recent'): array
    {
        $file = storage_path(self::FILE_PATH);
        if (!file_exists($file)) {
            return [];
        }

        $mtime = filemtime($file);
        $cacheKey = "animals:inventory:{$mtime}:{$sort}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($file, $sort) {
            $animals = $this->loadJson($file);
            return $this->applySorting($animals, $sort);
        });
    }

    /**
     * Get filtered and active animals only
     */
    public function getActiveAnimals(string $sort = 'recent'): array
    {
        $animals = $this->getAnimals($sort);
        
        return array_filter($animals, fn($a) => 
            ($a['State'] ?? '') === 'For Sale' && ($a['Enabled'] ?? '') === 'Active'
        );
    }

    /**
     * Load and parse JSON file
     */
    private function loadJson(string $file): array
    {
        $json = @file_get_contents($file);
        if (!$json) {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Apply sorting to animals array
     */
    private function applySorting(array $animals, string $sort): array
    {
        return match($sort) {
            'recent'        => $this->sortByLastUpdate($animals),
            'price-low'     => $this->sortByPrice($animals, 'asc'),
            'price-high'    => $this->sortByPrice($animals, 'desc'),
            'date-new'      => $this->sortByBirthDate($animals),
            'category'      => $this->sortByCategory($animals, 'asc'),
            'category-desc' => $this->sortByCategory($animals, 'desc'),
            default         => $this->sortByLastUpdate($animals),
        };
    }

    private function sortByLastUpdate(array $animals): array
    {
        usort($animals, fn($a, $b) => 
            strtotime($b['Last_Update**'] ?? 0) - strtotime($a['Last_Update**'] ?? 0)
        );
        return $animals;
    }

    private function sortByPrice(array $animals, string $direction): array
    {
        usort($animals, fn($a, $b) => 
            $direction === 'asc'
                ? ($a['Price'] ?? 0) - ($b['Price'] ?? 0)
                : ($b['Price'] ?? 0) - ($a['Price'] ?? 0)
        );
        return $animals;
    }

    private function sortByBirthDate(array $animals): array
    {
        usort($animals, fn($a, $b) => 
            strtotime($b['Dob'] ?? 0) - strtotime($a['Dob'] ?? 0)
        );
        return $animals;
    }

    private function sortByCategory(array $animals, string $direction): array
    {
        usort($animals, fn($a, $b) => 
            $direction === 'asc'
                ? strcmp($a['Category*'] ?? '', $b['Category*'] ?? '')
                : strcmp($b['Category*'] ?? '', $a['Category*'] ?? '')
        );
        return $animals;
    }
}
```

### Step 2: Update Welcome Route

**File: `routes/web.php` - Replace the welcome route**

```php
Route::get('/', function (Request $request) {
    $sort = $request->query('sort', 'recent');
    
    // ✅ Use service instead of inline logic
    $service = app(\App\Services\AnimalInventoryService::class);
    $animals = $service->getActiveAnimals($sort);

    // Load species map for display
    $slugs = array_column($animals, 'Animal_Id*');
    $speciesMap = $slugs 
        ? Animal::whereIn('slug', $slugs)
                ->with('species:id,species,common_name')
                ->get()
                ->keyBy('slug')
                ->map(fn($a) => $a->species)
        : [];

    return view('welcome', [
        'animals'     => $animals,
        'currentSort' => $sort,
        'speciesMap'  => $speciesMap,
    ]);
})->name('welcome');
```

---

## Phase 4: Image Lazy Loading (Low Effort)

### Step 1: Create Responsive Image Component

**File: `app/View/Components/ResponsiveImage.php`**

```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ResponsiveImage extends Component
{
    public function __construct(
        public string $url,
        public string $alt = '',
        public string $class = 'w-full object-cover',
    ) {}

    public function render()
    {
        return view('components.responsive-image');
    }
}
```

### Step 2: Create Component View

**File: `resources/views/components/responsive-image.blade.php`**

```blade
<img 
    src="{{ $url }}" 
    alt="{{ $alt }}"
    class="{{ $class }}"
    loading="lazy"
    decoding="async"
/>
```

### Step 3: Use in Animal Card

**File: `resources/views/animals/index.blade.php`**

```blade
@foreach ($animals as $animal)
    @php $thumb = $animal->media->first(); @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition flex flex-col">
        <a href="{{ route('animals.show', $animal) }}" class="relative block">
            @if ($thumb)
                {{-- ✅ Use responsive image component --}}
                <x-responsive-image 
                    :url="$thumb->url" 
                    :alt="$animal->pet_name"
                    class="w-full aspect-square object-cover"
                />
            @else
                <div class="w-full aspect-square bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-gray-400 text-sm">No photo</span>
                </div>
            @endif
        </a>
        {{-- Rest of card content --}}
    </div>
@endforeach
```

---

## Phase 5: Monitoring (Optional)

### Step 1: Enable Query Logging

**File: `app/Providers/AppServiceProvider.php`**

```php
<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Twitch\TwitchExtendSocialite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, TwitchExtendSocialite::class);

        // ✅ Log slow queries in production
        if (app()->isProduction()) {
            DB::listen(function (QueryExecuted $query) {
                if ($query->time > 1000) {  // Log queries slower than 1 second
                    Log::channel('queries')->warning('Slow query detected', [
                        'sql'      => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms'  => $query->time,
                    ]);
                }
            });
        }
    }
}
```

### Step 2: Create Query Log Channel

**File: `config/logging.php`**

```php
'channels' => [
    // ... existing channels ...
    
    'queries' => [
        'driver' => 'single',
        'path'   => storage_path('logs/queries.log'),
        'level'  => 'warning',
    ],
],
```

---

## Testing & Validation

### Run Tests After Each Phase

```bash
# Unit tests
php artisan test tests/Unit/Services/AnimalInventoryServiceTest.php

# Feature tests
php artisan test tests/Feature/AnimalControllerTest.php

# All tests
php artisan test

# Check for N+1 with debugbar
php artisan serve
# Visit http://localhost:8000/animals?page=1 and inspect queries in debugbar
```

### Query Count Verification

```bash
php artisan tinker

# Before optimization
>>> DB::enableQueryLog();
>>> Animal::where('status', 'published')->paginate(24);
>>> count(DB::getQueryLog());  # Should be high (50+)

# After optimization
>>> DB::flushQueryLog();
>>> Animal::where('status', 'published')
        ->with(['media', 'species', 'user'])
        ->paginate(24);
>>> count(DB::getQueryLog());  # Should be ~6
```

---

## Deployment Checklist

- [ ] Create feature branch: `git checkout -b perf/phase-1-indexes`
- [ ] Implement Phase 1 (indexes)
- [ ] Run `php artisan migrate` locally
- [ ] Run test suite: `php artisan test`
- [ ] Commit: `git commit -m "perf: add database indexes"`
- [ ] Create PR and request review
- [ ] Merge and deploy to staging
- [ ] Monitor performance metrics
- [ ] Deploy to production
- [ ] Update README.md changelog
- [ ] Proceed to next phase

---

## Rollback Instructions

If issues arise:

```bash
# Rollback indexes (Phase 1)
php artisan migrate:rollback --step=1

# Rollback controllers (Phase 2)
git revert <commit-hash>

# Clear caches
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Performance Monitoring (Post-Deployment)

Monitor these metrics in production:

```bash
# Check Laravel logs for slow queries
tail -f storage/logs/queries.log | grep "Slow query"

# Monitor database
SELECT * FROM pg_stat_statements ORDER BY mean_time DESC LIMIT 10;

# Check cache hit rate
redis-cli INFO stats | grep hits
```

---

**Estimated Timeline:**
- Phase 1: 2 hours
- Phase 2: 3 hours
- Phase 3: 2 hours
- Phase 4: 1 hour
- Phase 5: 0.5 hour
- **Total: 8.5 hours**

Start with Phase 1 for immediate impact!
