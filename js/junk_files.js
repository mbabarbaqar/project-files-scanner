$(document).ready(function(){

    //test.css
    let BASE_PATH = "http://localhost:7023/file_scanner";
    let API_URL = BASE_PATH + "/api/";

    let scanningOptions  =  {
        "selected_extensions": [".php",".js", ".html", ".css"], // file type extensions like .js .php etc.
        "source_directory": "images/",
        "target_directory": ["js/", "vendor/"],
        "include_files": true,
        "include_database": false
    };

    let listInnerFolders = {};
    let foldersName = '';
    let listInnerFiles = [];
    let initialListFoldersName = [
        {
            "name" : "images",
            "server_path" : "images/"
        },
        {
            "name" : "JS",
            "server_path" : "js/"
        }
    ];
    let listSubFoldersName = initialListFoldersName;
    let folderTree = [];
    let backUrl = '';
    let sortType = 'All';

    // DOM variables
    // General script
    let backButton = $("#backButton");
    let breadcrumbFolders = $("#breadcrumb-folders");
    let subFolderItem = $("#subFolders .folder-list").parent().html();
    let noSubFolders = $("#noSubFolders");
    let subFolderContainer = $("#subFolders");
    let statsListItems = $("#statsListItems");
    let statsListItemsDummy = $("#statsListItemsDummy");

    $( function(){
        renderScannedFiles();
    });

    /**
     * Scan selected folder
     * @var
     */
    function scanDirectory() {

        // initial-folders
        blockLoader('initial-folders', 'show');
        blockLoader('inner-folders', 'show');

        $.ajax({
            method: 'POST',
            url: API_URL + '?method=find_junk_records&type=folders',
            data: JSON.stringify(scanningOptions),
            contentType: "application/json; charset=utf-8",
            success: function (response) {
                response = JSON.parse(response);
                listInnerFolders = response;
                listSubFoldersName = response.sub_folders;

                //blockLoader('initial-folders', 'hide');
                //blockLoader('inner-folders', 'hide');

                setTimeout(function () {

                    scanDirectoryFiles();
                }, 1000);

                folderTree = {};
                if (response.sub_folders) {

                    let parentStr = response.parent_folder;
                    folderTree = parentStr.split('/');

                    if (folderTree) {

                        parentStr = stripTrailingSlash(response.parent_folder);

                        let parentStrArr = parentStr.split('/');
                        foldersName = parentStrArr.pop();

                        // Prepare back url
                        if (folderTree.length > 0) {
                            backUrl = parentStrArr.join('/');

                        }
                    }
                }
                renderScannedFiles();
            }
        });
    }

    function goBackDirectory(){

        if(backUrl != '' && folderTree.length > 0){

            scanningOptions.source_directory = backUrl;
            scanDirectory();
        }else{

            resetToInitialState();
        }
    }

    /**
     * Scan selected folder for files
     * @var
     */
    function scanDirectoryFiles() {

        blockLoader('files-listing', 'show', 'inner_files');

        $.ajax({
            method: 'POST',
            url: API_URL + '?method=find_junk_records&type=files',
            data: JSON.stringify(scanningOptions),
            contentType: "application/json; charset=utf-8",
            success: function (response) {

                listInnerFiles = response;
                blockLoader('files-listing', 'hide');
                $('.placeholder').hide();
            }
        });
    }

    function exploreFurther(path) {

        scanningOptions.source_directory = path;
        scanDirectory();
    }

    function fillCircles(customClass, percent) {

        let color = '#F8CB00';

        if(percent > 0) {

            color = '#26A69A';
        } else {

            color = '#F3565D';
        }

        setTimeout(function() {

            $('.'+customClass).easyPieChart({
                animate: 1000,
                size: 80,
                lineWidth: 5,
                barColor: color
            });
            //$('.'+customClass).data('easyPieChart').update(percent);
        }, 1000);
    }

    function blockLoader(divContainer, status, location) {

        let loaderText = 'Scanning...';

        if(location === 'inner_files'){

            loaderText = "Scanning files...";
        }

        if(status === 'show') {

            loader({
                target: '#' + divContainer,
                boxed: true,
                message: loaderText
            }, "show");
        } else {

            window.setTimeout(function() {

                loader({
                    target: '#' + divContainer
                }, "hide");
            }, 1000);
        }
    }

    function stripTrailingSlash(str) {

        if(str.substr(-1) === '/') {
            return str.substr(0, str.length - 1);
        }

        return str;
    }

    function sortJunkFiles(type) {

        sortType = type;
    }

    /**
     * DOM actions
     */
    function resetToInitialState() {

        // reset variables
        listInnerFiles = [];
        folderTree = [];
        listSubFoldersName = initialListFoldersName;

        // reset DOM elements
        $('.placeholder').show();
        backButton.hide();
    }

    function renderScannedFiles() {

        prepareBredCrumb();
        prepareListSubFolders();
        prepareStatsListItems();

        fillVariables();
        loadFolderEvents();
    }

    function prepareListSubFolders() {
        listInnerFiles = [];
        if (listSubFoldersName.length === 0) {

            noSubFolders.show();
            subFolderContainer.hide();
        }else {
            noSubFolders.hide();
            let subFolderItems = "";

            for (let i=0; i < listSubFoldersName.length; i++) {
                subFolderItems +=  subFolderItem.replaceAll("[folder_name]", listSubFoldersName[i].name)
                                   .replace("[server_path]", listSubFoldersName[i].server_path);
            }

            subFolderContainer.html(subFolderItems);
            subFolderContainer.show();
        }
    }

    function prepareBredCrumb() {
        folderTree = folderTree.filter(Boolean);
        if (folderTree) {
            let liItem = "<li class=\"folder-breadcrumb-item\">\n" +
                "<i class=\"fa fa-angle-right\"></i>\n" +
                "<a href=\"javascript:;\" class=\"folder-list-item\" data-path=\"\">{{nav}}</a>\n" +
                "</li>";

            let liItemHTML = "";
            for (let i=0; i < folderTree.length; i++) {
                liItemHTML +=  liItem.replace("{{nav}}", folderTree[i]);
            }

            breadcrumbFolders.find('li:not(:first-child)').remove();
            breadcrumbFolders.find("li:last").after(liItemHTML);
        }
    }

    function prepareStatsListItems() {
        statsListItems.hide();
        statsListItemsDummy.show();

        if (listInnerFolders.folders_search && listInnerFolders.folders_search.length > 0) {

            let sampleStatItem = statsListItems.find(".row").parent().html();

            console.log(listInnerFolders.folders_search);

            let folderStatsList = "";

            for (let i=0; i < listInnerFolders.folders_search.length; i++) {
                let prepareHTML = "";
                let innerFolders = listInnerFolders.folders_search[i];
                let folderFilterClass = "color-red";
                let uniqueClass = innerFolders.unique_class.replaceAll(" ", "-");

                if (innerFolders.is_matched > 0) {

                    folderFilterClass = "color-green";
                }

                prepareHTML = sampleStatItem.replace("{{folderFilterClass}}", folderFilterClass);
                prepareHTML = prepareHTML.replaceAll("{{innerFolders-unique_class}}", uniqueClass);
                prepareHTML = prepareHTML.replaceAll("[innerFolders-percentage]", innerFolders.percentage);
                prepareHTML = prepareHTML.replace("[innerFolders-name]", innerFolders.name);
                prepareHTML = prepareHTML.replace("{{innerFolders.server_path}}", innerFolders.server_path);

                folderStatsList += prepareHTML;
            }

            statsListItems.html(folderStatsList);

            statsListItems.show();
            statsListItemsDummy.hide();
        }
    }

    function loadFolderEvents() {
        $(".folder-list-item").click(function (){
            let path = $(this).data("path");
            exploreFurther(path);
        });

        if (folderTree.length > 0) {
            backButton.show();
            backButton.click(function () {
                goBackDirectory();
            });

            $("#reScanDirectory").show();
            $("#reScanDirectory").click(function () {

                scanDirectory();
            });
        }

        // Fill percentage circles with plugin init value
        if ($(".fillCircles").is(":visible")) {
            
            $(".fillCircles").each(function (){

                let uniqueClass = $(this).data("class");
                uniqueClass = uniqueClass.replaceAll(" ", "-");
                let percentage = $(this).data("percent");
                console.log(uniqueClass);
                fillCircles(uniqueClass, percentage);
            });
            
            $(".exploreFurther").click(function (){

                let path = $(this).data("path");
                exploreFurther(path);
            });
            
        }

    }

    function fillVariables() {
        $(".data-variables").each(function () {
            // Show parent folder
            if (listInnerFolders.parent_folder && this.id === "parent_folder_id") {
                $(this).text("[" + foldersName + "]");
            }
        });

    }

    function loader(options, state) {
        // if (state === "hide") {
        //     $(options.target).find(".loader-box").remove();
        // }
        //
        // let loaderHtml = '<div class="loader-box text-center">'+
        //             '<div class="spinner-grow" role="status">'+
        //                 '<span class="sr-only">Loading...</span>'+
        //             '</div>'+
        //         '</div>';
        //
        // if (options.target) { // element blocking
        //     var el = $(options.target);
        //     el.prepend(loaderHtml);
        // }
    }
});