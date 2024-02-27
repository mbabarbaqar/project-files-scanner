<?php
namespace Babardev\JunkFilesScanner;

use Babardev\JunkFilesScanner\Helpers\FileHelper;

class JunkFilesScanner {
    /**
     ** Search will be compiled within the specified folders by default
     */
    private $target_directory_locations = array(
        "ng/",
        "js/",
        "css/",
        "application/"
    );

    /**
     ** The selected files types that needed to be checked
     */
    private $selectedTypes = array(
        ".php",
        ".js",
        ".html",
        ".css",
        ".sql"
    );

    /**
     ** If true then botto level files will be taken to action otherwise top level directory.
     */
    private $include_files_from_source = TRUE;

    /**
     ** If you want to include databse in your required search.
     */
    private $include_database = TRUE;

    /**
     ** Make database search more specified by selecting table names.
     */
    private $includeTables = array(
        "admin_messages_frontend",
        "custom_ad_posts",
        "denial_reasons",
        "email_templates",
        "messages",
        "pages",
        "tips_of_day"
    );

    private $otherTables = array(
        "admin_user",
        "home_banners",
        "pet_more_pictures",
        "pets",
        "sponsor_icons",
        "sponsors",
        "testimonials",
        "user_pet_owners",
        "volunteers",
        "users",
        "guest_financial_assistance_applications",
        "guestuser_to_pets",
        "events"
    );

    private $rootFolder = "";

    function __construct($rootFolder = "") {

        $this->rootFolder = $rootFolder ?? $_SERVER["DOCUMENT_ROOT"];
    }

    public function index() {
        die('index');
        ini_set('memory_limit', '2048M');

        # Part One : List of folders to be detected as
        $sourceDirectory = 'images/';

        # Part Two: Create list of files where to search if folder is used or not
        $ngFiles1 = [];
        $ngFiles2 = [];
        $jsFiles = [];
        $cssFiles = [];
        $appFiles = [];
        //$sqlFiles = [];
        $allMergedFiles = [];
        $ngFiles1 = $this->get_dir_files_using_exten('ng/', '.js', $ngFiles1);
        $ngFiles2 = $this->get_dir_files_using_exten('ng/', '.html', $ngFiles2);
        $jsFiles = $this->get_dir_files_using_exten('js/', '.js', $jsFiles);
        $cssFiles = $this->get_dir_files_using_exten('css/', '.css', $cssFiles);
        //$sqlFiles = $this->get_dir_files_using_exten('helpFiles/', '.sql', $sqlFiles);
        $appFiles = $this->get_dir_files_using_exten('application/', '.php', $appFiles);
        $allMergedFiles = array_merge($ngFiles1, $ngFiles2, $jsFiles, $cssFiles, $appFiles);

        $ussege = $this->folders_usage_in_group_of_files($sourceDirectory, $allMergedFiles);
        echo json_encode($ussege);exit();
    }

    /**
     * API End-Point
     * Description:
     * @param $type : files/folders/single_file (Default:folders)
     * @return
     */
    public function findJunkRecords($type = NULL){
        ini_set('memory_limit', '2048M');
        $ussege = [];
        $params = json_decode(file_get_contents('php://input'), true);

        //echo json_encode($params);exit();

        # Files from which location you want to search/check
        $source_directory = (isset($params['source_directory'])&& !empty($params['source_directory'])) ? $params['source_directory'] : './';
        # Include location to where to find String OR array of directories ['emaple/', array('example/', 'example2/')]
        $target_directory_locations = (isset($params['target_directory'])&& !empty($params['target_directory'])) ? $params['target_directory'] : $this->target_directory_locations;
        # In which type of files to be searched against media has been used.
        $selectedTypes = (isset($params['selected_extensions'])&&!empty($params['selected_extensions'])) ? $params['selected_extensions'] : $this->selectedTypes;
        # Seach only for first level folders Like ('images/profiles/', 'images/logs/', 'images/new/') if false otherwsie will include every child media file within all folders
        $include_files_from_source = (isset($params['include_files'])&& !empty($params['include_files'])) ? $params['include_files'] : $this->include_files_from_source;
        # Include database if images are used in tables content.
        $include_database = (isset($params['include_database'])&& !empty($params['include_database'])) ? $params['include_database'] : $this->include_database;

        # Part: Create list of files where to search if folder is used or not
        $ngFiles = $jsFiles = $cssFiles = $appFiles = [];
        $allMergedFiles = [];
        if(!empty($target_directory_locations)){
            if(is_array($target_directory_locations)){
                $allMergedFiles = $this->get_dir_files_using_exten_multi_path($target_directory_locations, $selectedTypes);
            }else{
                $allMergedFiles = $this->get_dir_files_using_exten($target_directory_locations, $selectedTypes, $allMergedFiles);
            }
        }else{
            echo json_encode(array("error" => "Target locations not specified."));exit();
        }

        if(empty($source_directory)){
            echo json_encode(array("error" => "Source Directory not specified."));exit();
        }

        if(isset($type) && !empty($type) && $type == 'files'){
            $ussege = $this->files_usage_in_group_of_files($source_directory, $allMergedFiles, $include_database);
        }else if(isset($type) && !empty($type) && $type == 'single_file'){
            $ussege = $this->single_file_usage_in_group_of_files($source_directory, $allMergedFiles, $include_database);
        }else{
            $ussege = $this->folders_usage_in_group_of_files($source_directory, $allMergedFiles, $include_files_from_source, $include_database);
        }
        echo json_encode($ussege);exit();
    }

    function find_junk_files_database(){
        ini_set('memory_limit', '2048M');
        $params = json_decode(file_get_contents('php://input'), true);

        $files = $this->list_all_files_from_dir_top_level('images/');
        echo json_encode($files);exit();

        $search = $this->grep_db('images/');

        echo json_encode($search);exit();
    }


    /**
     * (Folders only)
     * Compile a list of files in a folder
     * @param string $folderPath
     */
    private function list_folders_from_dir($folderPath) {
        $folderPath = $this->rootFolder . "/" . $folderPath . "/";
        $folderPath = str_replace("//", "/", $folderPath);

        $folders = [];
        $allFilesFolders = FileHelper::get_dir_file_info($folderPath,TRUE, TRUE);


        foreach($allFilesFolders as $key => $value){
            if(is_dir($value['server_path'])){
                $value['server_path'] = str_replace($this->rootFolder, "", $value["server_path"]);
                $folders[] = $value;
            }
        }

        return $folders;
    }

    /**
     * (Files only, Top Level)
     * Compile a list of files in a folder
     * @param string $folder
     */
    private function list_all_files_from_dir_top_level($folder) {
        $files = [];
        $folder = $this->rootFolder . "/" . $folder . '/';
        $folder = str_replace("//", '/', $folder);
        $allFilesFolders = FileHelper::get_dir_file_info($folder,TRUE, TRUE);
        foreach($allFilesFolders as $key => $value){
            if(!is_dir($value['server_path'])){
                $files[] = $value;
            }
        }
        return $files;
    }

    /**
     * (Files only, including sub folders)
     * Compile a list of all files in one array from a folder including child folders
     * @param string $folder
     */
    private function list_all_files_from_dir_bottom_level($folder, $includeFolders = FALSE) {
        $files_array = FileHelper::get_filenames($folder, $includeFolders);
        return $files_array;
    }

    /**
     * (Get From Single Path)
     * @param String $path
     * @param Array/String $matchs : Example: array('.php', '.js', '.html', '.etc')
     * @param Array $files
     *
     * @return
     */
    private function get_dir_files_using_exten($path, $matchs, &$files){
        $path = $this->rootFolder . "/" . $path . "/";
        $path = str_replace("//", "/", $path);
        $dirs = glob($path."*");
        if(!empty($matchs)){
            if(is_array($matchs)){
                $filesNew = [];
                foreach($matchs as $key => $match){
                    $matchedFiles = glob($path.'*'.$match);
                    $filesNew = array_merge($filesNew, $matchedFiles);
                }
            }else{
                $filesNew = glob($path.'*'.$matchs);
            }
        }else{
            $filesNew = glob($path.'*');
        }
        foreach($filesNew as $file){
            if(is_file($file)){
                $files[] = $file;
            }
        }
        foreach($dirs as $dir){
            if(is_dir($dir)){
                //$dir = $dir . "/";
                $this->get_dir_files_using_exten($dir,$matchs, $files);
            }
        }
        return $files;
    }

    /**
     * (Get From Multiple Paths list)
     * @param Array $paths : Example[array('example/', 'example2/', 'example3')]
     * @param Array/String $matchs : Example: array('.php', '.js', '.html', '.etc')
     * @param Array $files
     *
     * @return
     */
    private function get_dir_files_using_exten_multi_path($paths, $matchs){
        $allFiles = [];
        if(is_array($paths)){
            foreach($paths as $key => $path){
                $files = [];
                $files = $this->get_dir_files_using_exten($path, $matchs, $files);
                $allFiles = array_merge($allFiles, $files);
            }
        }
        return($allFiles);
    }

    /**
     * (Files & Folders both)
     * Compile a list of files in a folder
     * @param string $folder
     */
    private function list_files_in_folder($folder, $directoryLevel = -1) {
        $files_array = FileHelper::directory_map('./' . $folder, $directoryLevel);
        return $files_array;
    }



    /**
     * Get ussage of listed directoies from the given locations of files
     * @param $sourceDirs : files location that be searched
     * @param $target_files
     * @param $findRegardingFils : IF true then, will also search using inner files of the directory, will not search only with directory name but also using containing files.
     * @return
     */
    private function folders_usage_in_group_of_files($sourceDirs, $target_files, $findRegardingFils = FALSE, $includeDatabase = FALSE) {
        $matches = [];
        $usage = [];
        $innerMatches = [];
        $averageMatchCount = 0;
        $listFolders = [];
        $listFolders = $this->list_folders_from_dir($sourceDirs);
        $sourceDirs = $sourceDirs . '/';
        $sourceDirs = str_replace("//", '/', $sourceDirs);
        $usage['sub_folders'] = $listFolders;
        $usage['parent_folder'] = $sourceDirs;
        $usage['folders_search'] = [];
        if($listFolders && $target_files){
            foreach($listFolders as $sourceDir){
                $innerFiles = [];
                $innerMatchedFiles = [];
                $averageAllCount = 0;
                if($findRegardingFils){
                    $innerFiles = $this->list_all_files_from_dir_bottom_level($sourceDir['server_path']);
                    $averageAllCount = count($innerFiles);
                }
                $sourceDirReg = str_replace("/","\/",$sourceDir['server_path']);
                $totalCount = 0;
                $innertotalCount = 0;
                $matchingFiles = [];
                $matchingInnerFiles = [];
                $databaseSearch = [];
                foreach($target_files as $target_file) {
                    $target_file_full = $_SERVER['DOCUMENT_ROOT'].'/'.$target_file;
                    $target_file_full = str_replace('//', '/', $target_file_full);
                    $target_file_content = FileHelper::read_file($target_file_full);
                    $pattern = "/($sourceDirReg)/";
                    $Count = @preg_match_all($pattern, $target_file_content, $matches);
                    if (!empty($matches[0])){
                        $totalCount = $totalCount + $Count;
                        $matchingFiles[] = $sourceDir['server_path'] . " Exists in -> " . $target_file;
                    }
                    # Search regarding inner files
                    if($findRegardingFils && !empty($innerFiles)){
                        foreach($innerFiles as $innerFile){
                            $pattern = "/($innerFile)/";
                            $Count2 = @preg_match_all($pattern, $target_file_content, $innerMatches);
                            if (!empty($innerMatches[0]) && !in_array($innerFile, array('index.html'))){
                                $innertotalCount = $innertotalCount + $Count2;
                                $matchingInnerFiles[] = $innerFile . " Exists in -> " . $target_file;
                                if(!in_array($innerFile, $innerMatchedFiles)){
                                    $innerMatchedFiles[] = $innerFile;
                                }
                            }
                        }
                    }
                }
                # Show response
                $resultResponse = array(
                    'name' => $sourceDir['name'],
                    'size' => $sourceDir['size'],
                    'server_path' => $sourceDir['server_path'],
                    'relative_path' => $sourceDir['relative_path'],
                    'unique_class' => str_replace('/', '-', $sourceDir['server_path']),
                    'percentage' => 0,
                    'is_matched' => ($totalCount > 0) ? TRUE : FALSE,
                    'folder_match' => $totalCount,
                    'files_match' => 0,
                    'files_location' => $matchingFiles,
                );
                # Include inner files result
                if($findRegardingFils){
                    $resultResponse['files_match'] = $innertotalCount;
                    $resultResponse['files_location'] = array_merge($matchingFiles, $matchingInnerFiles);
                }
                # Include database for more accurate searches
                # Include database search results
                if($includeDatabase){
                    $databaseSearch = $this->grep_db($sourceDir['server_path']);
                    $resultResponse['database_match'] = count($databaseSearch);
                    $resultResponse['database_locations'] = $databaseSearch;
                }
                if($totalCount > 0 || $innertotalCount > 0 || count($databaseSearch) > 0){
                    $resultResponse['is_matched'] = TRUE;
                }
                $resultResponse['total_files'] = $averageAllCount;
                $resultResponse['total_files_matched'] = count($innerMatchedFiles);

                if(count($innerMatchedFiles) > 0){
                    $percentage = round((count($innerMatchedFiles) / $averageAllCount) * 100);
                }else{
                    if($resultResponse['database_match'] == TRUE){
                        $percentage = 40;
                    }else{
                        $percentage = (count($resultResponse['files_location']) > 0) ? 10 : 0;
                    }
                }

                $resultResponse['percentage'] = $percentage;

                $usage['folders_search'][] = $resultResponse;
            }
        }
        return $usage;
    }

    /**
     * Get Junk Files of the current selected folder
     * @param String $sourceDirs
     * @param Array $target_files
     * @param Boolean $includeDatabase
     *
     * @return
     */
    private function files_usage_in_group_of_files($sourceDirs = NULL, $target_files = [], $includeDatabase = FALSE) {
        $innerMatches = [];
        $innerFiles = [];
        $usage = [];
        $filesMatchedArray = [];
        $totalMatched = 0;
        if($sourceDirs && $target_files){
            $innerFiles = $this->list_all_files_from_dir_top_level($sourceDirs);
            $usage['parent_folder'] = $sourceDirs;
            $usage['total_files'] = count($innerFiles);
            $usage['used_files'] = 0;
            $usage['junk_files'] = 0;
            $usage['files_list'] = [];
            if(!empty($innerFiles)){
                foreach($innerFiles as $innerFile){
                    if(!isset($innerFile['server_path']) || !isset($innerFile['name'])){
                        break;
                    }
                    $innertotalCount = 0;
                    $sourceDirReg = str_replace("/","\/",$innerFile['server_path']);
                    $pattern = "/($sourceDirReg)/";
                    foreach($target_files as $target_file) {
                        if(in_array($innerFile['server_path'], $filesMatchedArray)){
                            break;
                        }
                        $target_file_full = $_SERVER['DOCUMENT_ROOT'].'/'.$target_file;
                        $target_file_full = str_replace('//', '/', $target_file_full);
                        $target_file_content = FileHelper::read_file($target_file_full);

                        $Count2 = @preg_match_all($pattern, $target_file_content, $innerMatches);
                        if (!empty($innerMatches[0]) && !in_array($innerFile['server_path'], array('index.html'))){
                            $innertotalCount = $innertotalCount + $Count2;
                            $filesMatchedArray[] = $innerFile['server_path'];
                        }
                        # Search regarding inner files
                    }
                    # Show response
                    $resultResponse = array(
                        'name' => $innerFile['name'],
                        'source_name' => $innerFile['server_path'],
                        'is_matched' => ($innertotalCount > 0) ? TRUE : FALSE
                    );
                    # Include database for more accurate searches
                    # Include database search results
                    if($includeDatabase){
                        $databaseSearch = $this->grep_db($innerFile['server_path'], 'List_Files');
                        $resultResponse['is_matched'] = (count($databaseSearch) > 0 || $innertotalCount > 0) ? TRUE : FALSE;
                    }
                    $usage['files_list'][] = $resultResponse;
                    if($resultResponse['is_matched'] == TRUE){
                        $totalMatched = $totalMatched + 1;
                    }
                }
                $usage['used_files'] = $totalMatched;
                $usage['junk_files'] = count($innerFiles) - $totalMatched;
            }
        }
        return $usage;
    }

    /**
     * Find Locations of a single file
     * @param String $innerFile
     * @param Array $target_files
     * @param Boolean $includeDatabase
     *
     * @return Array $usage
     */
    private function single_file_usage_in_group_of_files($innerFile = NULL, $target_files = [], $includeDatabase = FALSE) {
        $innerMatches = [];
        $innerFiles = [];
        $usage = [];
        if($innerFile && $target_files){
            $matchingInnerFiles = [];
            $innertotalCount = 0;
            $sourceDirReg = str_replace("/","\/",$innerFile);
            $pattern = "/($sourceDirReg)/";
            foreach($target_files as $target_file) {
                $target_file_full = $this->rootFolder .'/'. $target_file;
                $target_file_full = str_replace('//', '/', $target_file_full);
                $target_file_content = FileHelper::read_file($target_file_full);

                $Count2 = @preg_match_all($pattern, $target_file_content, $innerMatches);
                if (!empty($innerMatches[0]) && !in_array($innerFile, array('index.html'))){
                    $innertotalCount = $innertotalCount + $Count2;
                    $matchingInnerFiles[] = $innerFile . " Exists in -> " . $target_file;
                }
                # Search regarding inner files
            }
            # Show response
            $resultResponse = array(
                'source_name' => $innerFile,
                'is_matched' => ($innertotalCount > 0) ? TRUE : FALSE,
                'matched_location' => $matchingInnerFiles,
            );
            # Include database for more accurate searches
            # Include database search results
            if($includeDatabase){
                $databaseSearch = $this->grep_db($innerFile, 'Single_File');
                $resultResponse['database_locations'] = $databaseSearch;
                $resultResponse['is_matched'] = (count($databaseSearch) > 0 || $innertotalCount > 0) ? TRUE : FALSE;

            }
            $usage[] = $resultResponse;
        }
        return $usage;
    }


    /**
     * Grep DB
     *
     * Completes a text search on a MYSQL databases' tables' data
     * And returns the matching rows from all tables
     * Can be fairly easily changed to work in any/no framework
     *
     * @param 	array/Str	$search_values :Array/String of Search Terms, Example: 'images, images/bug. array('example')' OR 'badtext'
     * @param	Str	$searchType: List_Folder/List_Files/Single_File
     * @return	array	Search Results
     */
    private function grep_db($search_values, $searchType = 'List_Folder', $showResultLocations = TRUE)
    {
        # Init vars
        if(isset($this->db->database)){
            $db_name = $this->db->database;
        }else{
            return [];
        }

        $table_fields = [];
        $cumulative_results = [];
        $list_searched_thing = [];
        # Pull all table columns that have character data types
        $result = $this->db->query("
			SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
			FROM  `INFORMATION_SCHEMA`.`COLUMNS` 
			WHERE  `TABLE_SCHEMA` =  '{$db_name}'
			AND `DATA_TYPE` IN ('varchar', 'char', 'text', 'blob')
			")->result_array();
        # Build table-keyed columns so we know which to query
        foreach ( $result  as $o )
        {
            $table_fields[$o['TABLE_NAME']][] = $o['COLUMN_NAME'];
        }

        # Build search query to pull the affected rows
        # Search Each Row for matches
        foreach($table_fields as $table_name => $fields)
        {
            # Include specified tables from database if configured in the top.
            if(!empty($this->includeTables)){
                if(!in_array($table_name, $this->includeTables)){
                    continue;
                }
            }

            # Clear search array
            $search_array = [];
            $search_string = '';
            $searchedValue = '';
            # Add a search for each search match
            foreach($fields as $field)
            {
                if(is_array($search_values)){
                    foreach($search_values as $value){
                        $search_array[] = " `{$field}` LIKE '%{$value}%' ";
                    }
                    $searchedValue = implode(',', $search_values);
                }else{
                    $search_array[] = " `{$field}` LIKE '%{$search_values}%' ";
                    $searchedValue = $search_values;
                }
            }
            $search_string = implode (' OR ', $search_array);
            # Implode $search_array
            $query_string = "SELECT * FROM `{$table_name}` WHERE {$search_string}";
            $table_results = $this->db->query($query_string)->result_array();

            # Check at least one database record exists : no need to execute all database in listing.
            # Will fetch full list in other cases.
            if($searchType == 'List_Files' && $table_results){
                if(in_array($searchedValue, $list_searched_thing)){
                    break;
                }
                $list_searched_thing[] = $searchedValue;
            }

            if($showResultLocations){
                if($table_results){
                    $cumulative_results[] = "{$searchedValue} exists in database table [{$table_name}]";
                }
            }else{
                $cumulative_results = array_merge($cumulative_results, $table_results);
            }
        }
        return $cumulative_results;
    }

}
