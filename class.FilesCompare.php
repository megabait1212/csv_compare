<?php

class FilesCompare {

    /**
     * Compare 2 csv files for changes by values of 1 column
     * if product doesn't exist in "today" it's mean we'll deleted it from catalog
     * if product doesn't exist in "yesterday" it's mean this is new product and we'll add it to catalog
     *
     * @param string $file1 Path to yesterday csv file
     * @param string $file2 Path to today csv file
     * @param string $prefix Filename prefix, by default it's empty
     * @param boolean $same If false it will return csv file with list of different values (new and removed values),
     * if true it will return list of the same values from 2 files
     * @param string $file1_column_name Name of column in file
     * @param string $searchIn which file use for search, possible values $file1 or $file2, both. If set to one file it will output 1 csv file with data of parent file. If it set to both it will output data of both file
     * @return file return csv file with list of changed products (new and deleted)
     */
    public function compareFiles($file1, $file2, $prefix = '', $same = true, $file1_column_name, $file2_column_name, $searchIn = '') {
        if (empty($file1 || $file2)) {
            die('Please add files for comparison');
        }
        if (empty($file1_column_name || $file2_column_name)) {
            die('Please add column name to compare');
        }
        $old = $this->_getContent($file1);
        $new = $this->_getContent($file2);
        $oldItems = $this->_prepareFile($old, $file1_column_name);
        $newItems = $this->_prepareFile($new, $file2_column_name);

        $updated = '';
        if ($same) {
            $sameItems = array_intersect($oldItems, $newItems);
            if ($searchIn === $file1) {
                $updated = $this->_searchProducts($sameItems, $old);
                $prefix = basename($file1, ".csv") . '-';
                $this->_fileWrite($updated, $prefix);
            } elseif ($searchIn === $file2) {
                $updated = $this->_searchProducts($sameItems, $new);
                $prefix = basename($file2, ".csv") . '-';
                $this->_fileWrite($updated, $prefix);
            } else {
                $updated1 = $this->_searchProducts($sameItems, $old);
                $updated2 = $this->_searchProducts($sameItems, $new);
                $prefix1 = basename($file1, ".csv") . '-';
                $prefix2 = basename($file2, ".csv") . '-';
                $this->_fileWrite($updated1, $prefix1);
                $this->_fileWrite($updated2, $prefix2);
            }

        } else {
            $deletedItems = array_diff($oldItems, $newItems);
            $updatedItems = array_diff($newItems, $oldItems);
            $oldList = $this->_searchProducts($deletedItems, $old);
            $updatedOldList = array();
            foreach ($oldList as $oldItem) {
                $oldItem['qty'] = 0;
                $updatedOldList[] = $oldItem;
            }
            $newList = $this->_searchProducts($updatedItems, $new);
            $updated = array_merge($updatedOldList, $newList);
            $this->_fileWrite($updated, $prefix);
        }
    }

    /**
     * Write changed products to file, if file already exists it will overwrite all data
     * @param string $file Path to file (no difference today or yesterday) it's just to generate header 
     * @param array $updated Array of changed products
     * @param type $prefix arabesque or primedirect without dash
     * @return file return csv file with list of changed products (new and deleted)
     */
    protected function _fileWrite($updated, $prefix) {
        if (empty($updated)) {
            die('No changes');
        } else {
            $today = date("njY");
            $new = fopen('./files/' . $prefix . 'update-' . $today . '.csv', 'w');
            $br = "\n";
            foreach ($updated as $update) {
                fwrite($new, '"' . implode('";"', array_values($update)) . '"' . $br);
            }
            fclose($new);
        }
    }

    /**
     * Search product by column name
     * @param string $needles column name
     * @param array $haystack Array
     * @return array Array of found values
     */
    protected function _searchProducts($needles, $haystack) {
        $newList = array();
        foreach ($needles as $needle) {
            $id = $this->_recursiveArraySearch($needle, $haystack);
            $newList[] = $haystack[$id];
        }
        return $newList;
    }

    /**
     * Search in multidimensional array
     * @param string $needle
     * @param array $haystack
     * @return false or index of parent array
     */
    protected function _recursiveArraySearch($needle, $haystack) {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value OR ( is_array($value) && $this->_recursiveArraySearch($needle, $value) !== false)) {
                return $current_key;
            }
        }
        return false;
    }

    /**
     * Get header of csv file
     * @param string $file File title 
     * @param string $path Path to file folder
     * @return array Array of header's elements
     */
    protected function _getHeader($file) {
        $file = fopen('./files/' . $file, 'r');
        return $header = fgetcsv($file, 0, ';');
    }

    /**
     * Get content of csv file
     * @param string $file Path to file
     * @param string $path Path to file folder
     * @return array Array of csv file values by line
     */
    protected function _getContent($file) {
        $header = $this->_getHeader($file);
        $file = fopen('./files/' . $file, 'r');
        $data = array();
        while ($row = fgetcsv($file, 0, ';')) {
            $arr = array_combine($header, $row);
            foreach ($header as $i => $col) {
                $arr[$col] = $row[$i];
            }
            $data[] = $arr;
        }
        return $data;
    }

    /**
     * Prepare data for comparison
     * @param array $data 
     * @param string $column_name Column title
     * @return array of catalog numbers
     */
    protected function _prepareFile($data, $column_name) {
        if (empty($data)) {
            die('No data');
        }
        $col = array();
        foreach ($data as $value) {
            $col[] = $value[$column_name];
        }

        return $col;
    }

}