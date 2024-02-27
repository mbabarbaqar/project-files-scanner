# Junk Files Scanner
*welcome*

#### Ussage example
1. End Point:
Get all listed folders within a directory
`http://beta.dod.opt/api/junk_browser/find_junk_records/files`
2. End Point: Get files only from a selected directory
`http://beta.dod.opt/api/junk_browser/find_junk_records/folders`
3. End Point: Get single file info and ussage report
`http://beta.dod.opt/api/junk_browser/find_junk_records/single_file`

#### Request Body :
```js
{
"selected_extensions" : [".php",".js", ".html", ".css", ".sql"],
"source_directory" : "images/",
"target_directory" : "ng"/["ng/", "css/", "application"],
"include_files" : true,
"include_database" : true
}

Content-Type : application/json

```

```bash
composer require babardev/junk-files-scanner
```