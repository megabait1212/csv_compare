# csv_compare
Php class for comparison 2 csv files by one column.

**How to use:**
create folder with name 'files' then add next lines to your php document

```
require_once('class.FilesCompare.php');
$compare = new FilesCompare();
$compare->compareFiles($file1, $file2, $prefix = '', $same = true, $file1_column_name, $file2_column_name, $searchIn = '');
```

     * Compare 2 csv files for changes by values of 1 column
     * @param string $file1 Path to previous version of csv file
     * @param string $file2 Path to current version of csv file
     * @param string $prefix If needed you can add filename prefix, by default it's empty
     * @param boolean $same If false it will return csv file with list of different values (new and removed values),
     * if true it will return list of the same values from 2 files
     * @param string $file1_column_name Name of column for comparison in file1
     * @param string $file2_column_name Name of column for comparison in file2
     * @param string $searchIn which file use for search, possible values $file1, $file2 or both. 
     * If set to one file it will output 1 csv file with data of parent file. If it set to both it will output data of both files
     * @return return csv file with changes (new and deleted) or with the same values

**TODO**
* Custom delimiter, when read files and output file
* GUI
