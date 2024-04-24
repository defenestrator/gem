## About gemreptiles.com v3

We are a boutique hobbyist reptile breeding operation owned and operated by the love of my life, Becky and myself. I do all the web/business development, some of the husbandry and all of the marketing. Becky does most of the husbandry, even though I have the most experience with reptiles, we have more than 60 years of combined experience keeping and breeding exotics.  

Thbe primary market for selling reptiles online is morphmarket.com so our site must have facilities for data export in a format their site can ingest, so we can bulk upload our available animals.

### Example Schema for Bulk Import 2.x on morphmarket.com

```json
[
  {
    "Category": "ball python",
    "Title": "Firefly Ball Python",
    "Maturity": "adult",
    "Price": "1500",
    "Serial": "MM-100",
    "Quantity": "1",
    "Sex": "male",
    "Dob": "02-28-2008",
    "Weight": "150",
    "Length": "1.2",
    "Length_Type": "total_length_m",
    "Traits": "pastel pinstripe clown",
    "Clutch": "2015-9",
    "Photo_Urls": "https://example.com/some-test-photo.png",
    "Video_Url": "https://youtube.com/watch?v=1234",
    "Proven_Breeder": "yes",
    "Desc": "Fantastic eater and very docile.",
    "Is_Group": "TRUE",
    "Availability": "available",
    "Origin": "domestically produced",
    "Prey_State": "live",
    "Prey_Food": "rat",
    "Min_Shipping": "50",
    "Max_Shipping": "100",
    "Is_Rep_Photo": "FALSE",
    "Is_Negotiable": "TRUE",
    "Is_For_Trade": "TRUE"
  },
  {
    "Category": "",
    "Title": "",
    "Maturity": "",
    "Price": "",
    "Serial": "",
    "Quantity": "",
    "Sex": "",
    "Dob": "",
    "Weight": "",
    "Length": "",
    "Length_Type": "",
    "Traits": [],
    "Clutch": "",
    "Photo_Urls": [],
    "Video_Url": "",
    "Proven_Breeder": "",
    "Desc": "",
    "Is_Group": "",
    "Availability": "",
    "Origin": "",
    "Prey_State": "",
    "Prey_Food": "",
    "Min_Shipping": "",
    "Max_Shipping": "",
    "Is_Rep_Photo": "",
    "Is_Negotiable": "",
    "Is_For_Trade": ""
  },
]
```
### Notes on technology 

This is a Laravel app, you can install it and run it as your own reptile store, you could even just rebrand it and you're off to the races. That is once I build the dang thing. Docs will be in this, and possibly other .md files within the code repository itself. Everything except your secrets belongs in git, kiddos. Okay maybe also BLOBS, put those in S3 or similar, not your dang database and not in git, silly geese.

We use htmx and AlpineJS on the front end, with an emphasis on htmx, which I think is really cool.

The source is open, our content, logos and UI designs are &copy; All rights Reserved 2024

### Changelog

#### 2024-04-24 Major overhaul and launch preparation, one quarter-year late.

- Updated to Laravel 11
- removed uuids from models 
- removed Dyrynda's deprecated uuid packages
- updated package.json dependencies
- updated composer.json dependencies 
- removed Daisy UI
- added @tailwind/typography
- updated root .gitignore