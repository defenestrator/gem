@use('Laravel\Pulse\Facades\Pulse')
@props(['cols' => 12, 'fullWidth' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Gem Monitor</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600" rel="stylesheet" />
        <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACQFBMVEUAAAD6WlXyWmb2Wl77WlXxWmPqWmzpWmzdWnvhWnjjWnXUWojaWoDTWonMWpHLWpLAWqDCWp7CWp3DWp23Wqu7Wqe6WqisWrm2Wq2xWrPsWWalWsKsWrqkWsSGWumDWu2BWu97Wvd7WvZ6Wvh8WvZ5Wvn4Wlr4WlrxWmPyWmHyWmHwWmTpWm3qWmzqWmzpWm3gWnjhWnjgWnjRWovXWoTXWoTMWpHNWpDNWpDLWpPAWqDBWp/AWqDDWpzEWpvEWpvEWpvDWp23Wqy6Wqi6Wqi6Wqi7Wqe7Wqe6Wqe6WqiwWrWxWrOxWrSrWruxWrSyWrKxWrOwWrSlWsOnWsCnWr+mWsGnWr+oWr6oWr+nWsCaWtCaWtCaWs+aWtCdWs2fWsqeWsqeWsudWsyeWsueWsufWsmfWsqeWsucWs6aWtCaWs+aWtCZWtGUWteVWteXWtSVWtaUWteUWteVWteXWtSVWtaVWteUWteSWtqUWtiUWteUWtePWt6OWt+OWt+OWt+OWt+XWtSMWuKLWuOLWuKMWuGLWuOLWuOKWuSLWuOLWuKNWuCOWt+QWt2DWu2CWu+CWu6EWuuCWu6BWu+BWu+DWu17WvZ7Wvd7Wvd7Wvd7Wvd7Wvd8WvZ6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6Wvh6WvjhWnfXWoPNWo/EWpu7Wqe6WqexWrOnWr+oWr+eWsuUWteLWuOBWu97Wvd6Wvj///+vWn4hAAAAsHRSTlMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4NFbGpEEL28DVy/l0CpYoU0bUHM3EaNe/+2RoTwHNi0+PzOWz8sQWVj7VhI9beLsJTiI4xZWhmpsvY+Xjk6iZcuQcnaVIMt/JNk9j8xgs289wtuO9ESIeKi2ADWvyXGdjzn/jgjnEVKeZkBrP6/XEKvPA2iMgXgtIVW/74XDrVhwImyqMOGAsBFqm/VNAAAAABYktHRL/T27NVAAAAB3RJTUUH5wsQDgkSyLkAHwAAASpJREFUOMtjYBh2gJFJTZ2ZBY8CVg1NLW1WPArYdHT19Nlxy3NwGmwwNOLkwqmA29hk40ZTHl6cCvjMzDdtsrDkxyYnIGhlbSNka2e/2cFRGJsCESfnLS6irlvd3D08xbDIi0t4eW/z8fXb5h+wLVBSClOBdFDw9u0hoWE7wiO2R8rIoshFRcfExMTG7YxPSExKTklN25mekQkSAoOs7ByG3F1gkJdfsKuwqLiktKy8onIXHFRVM9TU1gFBfUNjU/Pu3S1yrW3tHZ1d3XUQUN/TC7dKvq9/z54JChMn7Zk8ZaqiHKY7laZN3ztjpqLyrL17Z89RweJTVeW5++bNV1ZesHDRYmVVbGGlvGTpsuXKyitWrlqtjDUmlJevWausrLxu/Wpl7ApGNgAAUsxoMcT6INsAAAAldEVYdGRhdGU6Y3JlYXRlADIwMjMtMTEtMTZUMTQ6MDk6MTgrMDA6MDANsDzYAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDIzLTExLTE2VDE0OjA5OjE4KzAwOjAwfO2EZAAAAFd6VFh0UmF3IHByb2ZpbGUgdHlwZSBpcHRjAAB4nOPyDAhxVigoyk/LzEnlUgADIwsuYwsTIxNLkxQDEyBEgDTDZAMjs1Qgy9jUyMTMxBzEB8uASKBKLgDqFxF08kI1lQAAAABJRU5ErkJggg==" rel="icon" type="image/x-icon">

        {!! Pulse::css() !!}
        @livewireStyles

        {!! Pulse::js() !!}
        @livewireScriptConfig
    </head>
    <body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased">
        <div class="min-h-screen">
            <header class="px-5">
                <div class="{{ $fullWidth ? '' : 'container' }} py-3 sm:py-5 mx-auto border-b border-gray-200 dark:border-gray-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img src="/favicon.png" alt="Gem Reptiles" class="h-8 w-auto">
                            <span class="text-lg sm:text-2xl text-gray-700 dark:text-gray-300 font-medium"><b class="font-bold">Gem</b> Monitor</span>
                        </div>
                        <div class="flex items-center gap-3 sm:gap-6">
                            <a href="{{ route('dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium cursor-pointer">
                                &larr; Dashboard
                            </a>
                            <livewire:pulse.period-selector />
                            <x-pulse::theme-switcher />
                        </div>
                    </div>
                </div>
            </header>

            <main class="pt-6 px-6 pb-12">
                <div {{ $attributes->merge(['class' => "mx-auto grid default:grid-cols-{$cols} default:gap-6" . ($fullWidth ? '' : ' container')]) }}>
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
