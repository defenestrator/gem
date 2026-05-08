Create a new feature for selecting the highlighted image shown on the index pages, from all associated media records related to a given Animal or Species record. This feature requires the following list
- Compress the original image and sync it to S3 CDN (in production) or local storage/public (in local/dev/test environments)
- Create an appropriately-sized compressed thumbnail image if one does not exist: 100px wide for species, 400px wide for animals
- Stream the thumbnail image to DO Spaces in the associated directory
- On the "show" page for individual Animals and Species records:
  - Show a star icon to Admins only, below the image in the UI to indicate selected highlight (thumbnail) image to appear on the index page, and make it clickable to select which image is highlighted. The star operates like a radio select, in that it is mutually exclusive, but don't use a select element, use AlpineJS and Livewire.
- Do not cause regressions