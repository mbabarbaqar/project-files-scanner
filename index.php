<!-- END CONTENT -->
<link rel="stylesheet" type="text/css" href="./js/jstree/dist/themes/default/style.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
<script src="./js/jquery-3.7.1.js"></script>
<script src="./js/jstree/dist/jstree.min.js"></script>
<script src="./js/jquery-easypiechart/jquery.easypiechart.js"></script>
<script src="./js/junk_files.js"></script>
<script src="./js/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>

<!-- BEGIN CONTENT -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            Junk Files Scanner <small></small>
        </h3>

        <div class="row">
            <div class="col-md-3">
                <div class="portlet" style="box-shadow: none;">
                    <div class="portlet-title" style="border-bottom: 1px solid #26A69A !important;">
                        <div class="caption color-green">
                            <i class="fa fa-folder"></i> Scanned Folders
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <a class="btn green btn-sm data-variables" id="backButton" style="display: none;" href="javascript:;">
                                    ← Back
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="portlet-body" id="initial-folders" style="height: 100%;">

                        <div class="row">
                            <div class="col-md-12 color-green">
                                <ul id="breadcrumb-folders">
                                    <li>
                                        <i class="fa fa-folder-open"></i>
                                        <a href="javascript:;"> Root</a>
                                    </li>
                                </ul>
                            </div>
                        </div>


                        <div class="row scroller">
                            <div class="col-md-12" id="noSubFolders" style="display: none;">
                                <div class="folder-list color-grey">
                                    <i class="fa fa-folder-open"></i><br />
                                    <a href="javascript:;" title="Scanned folders.."> No folder </a>
                                </div>
                            </div>

                            <div class="col-md-12"  id="subFolders">
                                <div class="folder-list">
                                    <i class="fa fa-folder-open"></i><br />
                                    <a href="javascript:;" class="folder-list-item" title="Scan [folder_name].." data-path="[server_path]"> [folder_name] <span class="icon-arrow-right"></span></a>
                                </div>
                            </div>
                        </div>

                        <div class="margin-bottom-10 visible-sm">

                        </div>
                    </div>
                </div>

            </div>


            <div class="col-md-4">
                <div class="portlet" style="box-shadow: none;">
                    <div class="portlet-title" style="border-bottom: 1px solid #26A69A !important;">

                        <div class="caption color-green">
                            <i class="fa fa-folder-open"></i><span class="data-variables" id="parent_folder_id"></span> Inner Folders
                        </div>

                        <div class="actions">
                            <div class="btn-group">
                                <a class="btn green btn-sm" id="reScanDirectory" href="javascript:;" style="display: none;">
                                    <i class="fa fa-play"></i> Rescane
                                </a>
                            </div>
                        </div>
                    </div>


                    <div class="portlet-body" id="inner-folders">
                        <div class="row">
                            <div class="col-md-12 color-green">
                                <ul class="folder-breadcrumb">
                                    <li>
                                        <i class="fa fa-folder-open"></i>
                                        <a href="javascript:;"> Folders</a>
                                    </li>
                                    <li ng-if="folderTree" ng-repeat="nav in folderTree">
                                        <i class="fa fa-angle-right"></i>
                                        <a href="javascript:;">{{nav}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>



                        <div class="row scroller">
<!--                            <div class="margin-bottom-10 visible-sm">-->
<!--                            </div>-->

                            <div class="col-md-12" id="statsListItems" style="display: none;">
                                <div class="row">
                                    <div class="easy-pie-chart col-md-6 {{folderFilterClass}}">
                                        <div class="circle{{innerFolders-unique_class}} number visits fillCircles" data-class="circle{{innerFolders-unique_class}}" data-percent="{{innerFolders.percentage}}">
											<span>
											+[innerFolders-percentage] </span>
                                            %
                                        </div>
                                        <a class="title exploreFurther" href="javascript:;"  data-path="{{innerFolders.server_path}}" onClick="exploreFurther(innerFolders.server_path)">
                                            [innerFolders-name] <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                    <div class="folder-item-left col-md-6 col-sm-4 color-grey-dark">
                                        <div class="margin-top-10"></div>
                                        <i class="fa fa-database" ng-class="innerFolders.database_match > 0 ? 'color-green': 'color-red'"></i> &nbsp; Database matched <br />
                                        <i class="fa fa-folder-open" ng-class="innerFolders.folder_match > 0 ? 'color-green': 'color-red'"></i> &nbsp; Folder matched <br />
                                        <i class="fa fa-file-text" ng-class="innerFolders.files_match > 0 ? 'color-green': 'color-red'"></i> &nbsp; Inner files matched <br />
                                    </div>
                                </div>
                                <div class="margin-bottom-10 visible-sm">
                                </div>
                            </div>

                            <!--Dummy Pie Charts-->
                            <div class="row"  id="statsListItemsDummy">
                                <div class="margin-bottom-10 visible-sm">
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="easy-pie-chart">
                                                <div class="number visits" data-percent="50">
													<span class="color-grey">
													+00 %</span>

                                                </div>
                                                <a class="title" href="javascript:;">
                                                    <span class="color-grey">No folder</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="folder-item-left col-md-6 col-sm-4 color-grey">
                                            <div class="margin-top-10"></div>
                                            <i class="fa fa-database"></i> &nbsp; Database matched <br />
                                            <i class="fa fa-folder-open"></i> &nbsp; Folder matched <br />
                                            <i class="fa fa-file-text"></i> &nbsp; Inner files matched <br />
                                        </div>
                                    </div>
                                </div>

                                <div class="margin-bottom-10 visible-sm">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>



            <div class="col-md-5">
                <div class="portlet" style="box-shadow: none;">
                    <div class="portlet-title" style="border-bottom: 1px solid #26A69A !important;">
                        <div class="caption color-green">
                            <i class="fa fa-file-text"></i>
                            {{sortType}} Files
                            <span ng-if="sortType == 'All'" class="label label-success">{{listInnerFiles.total_files}}</span>
                            <span ng-if="sortType == 'Used'" class="label label-success">{{listInnerFiles.used_files}}</span>
                            <span ng-if="sortType == 'Junk'" class="label label-success">{{listInnerFiles.junk_files}}</span>
                        </div>
                        <div class="actions">
                            <!--<a href="javascript:;" class="btn default btn-sm">
                            <i class="fa fa-share icon-black"></i> Share </a>-->
                            <div class="btn-group">
                                <a class="btn btn-sm green" href="javascript:;" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-user"></i> File Status <i class="fa fa-angle-down "></i>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="javascript:;" onClick="sortJunkFiles('Junk')">
                                            <i class="fa fa-pencil"></i> Junk Files ({{listInnerFiles.junk_files}})</a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onClick="sortJunkFiles('Used')">
                                            <i class="fa fa-trash-o"></i> Used Files ({{listInnerFiles.used_files}})</a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onClick="sortJunkFiles('All')">
                                            <i class="fa fa-trash-o"></i> All Files ({{listInnerFiles.total_files}})</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body" id="files-listing">
                        <div class="row">
                            <div class="col-md-12 color-green">
                                <ul class="folder-breadcrumb">
                                    <li>
                                        <i class="fa fa-folder-open"></i>
                                        <a href="javascript:;"> Folders</a>
                                    </li>
                                    <li ng-if="folderTree" ng-repeat="nav in folderTree">
                                        <i class="fa fa-angle-right"></i>
                                        <a href="javascript:;">{{nav}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="scroller">
                            <div id="tree_1" class="tree-demo">
                                <ul>
                                    <li ng-if="listInnerFiles.files_list && listInnerFiles.files_list.length > 0" data-jstree='{ "opened" : true, "icon" : "fa fa-folder-open icon-state-success" }'>
                                        {{listInnerFiles.parent_folder}}
                                        <ul>
                                            <li ng-repeat="innerfile in listInnerFiles.files_list track by $index" ng-if="(sortType == 'All') || (innerfile.is_matched == true && sortType == 'Used') || (innerfile.is_matched == false && sortType == 'Junk')" id="js_{{$index}}" ng-class="innerfile.is_matched ? 'color-green':'color-red'">
                                                <a href="javascript:;"> {{innerfile.name}}  <span ng-if="innerfile.is_matched != true" class="fa fa-times"></span></a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="placeholder" data-jstree='{ "opened" : true, "disabled" : true, "icon" : "fa fa-folder-open"}'>
                                        Selected Folder
                                        <ul>
                                            <li data-jstree='{ "icon" : "fa fa-file icon-state-success ", "disabled" : true}'>
                                                <a href="javascript:;">
                                                    Listed file exists if have green icon. </a>
                                            </li>
                                            <li data-jstree='{ "icon" : "fa fa-warning icon-state-danger ","disabled" : true}'>
                                                Listed file does not exist if have red icon. <span class="fa fa-times"></span>
                                            </li>
                                            <li data-jstree='{ "disabled" : "true" }'>
                                                All sample listing here.
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="clearfix"></div>




    </div>
</div>

<script>
    jQuery(document).ready(function() {
        // UI Tree
        $('#tree_1').jstree({
            "core" : {
                "themes" : {
                    "responsive": false
                }
            },
            "types" : {
                "default" : {
                    "icon" : "fa fa-file icon-state-warning icon-lg"
                },
                "file" : {
                    "icon" : "fa fa-file icon-state-warning icon-lg"
                }
            },
            "plugins": ["types"]
        });

        $('.easy-pie-chart .number.visits').easyPieChart({
            animate: 1000,
            size: 80,
            lineWidth: 5,
            barColor: '#d0d1d2'
        });

        // END READY FUNCTION
    });


</script>
<style>
    .folder-list{text-align: center;padding-top: 40px;font-weight: 800;color: #26A69A}
    .folder-list a{color: #26A69A;}
    .folder-list i{font-size: 40px; margin-bottom: 17px;}
    .color-red{color: #F3565D;}

    .color-green{color: #26A69A;}
    .color-green a{color: #26A69A;}
    .color-green i{color: #26A69A !important;}

    .color-grey-dark{color: #b7b8b9;}

    .color-grey{color: #d0d1d2;}
    .color-grey a{color: #d0d1d2;}
    .color-grey i{color: #d0d1d2;}
    .hide-element{display: none;}

    .scroller
    {
        overflow-y: auto;
        scroll-behavior: smooth;
        height: 500px;
    }
    .scroller::-webkit-scrollbar-track
    {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        background-color: #F5F5F5;
    }

    .scroller::-webkit-scrollbar
    {
        width: 6px;
        background-color: #F5F5F5;
    }

    .scroller::-webkit-scrollbar-thumb
    {
        background-color: #26A69A;
    }
    .folder-breadcrumb {
        display: inline-block;
        float: left;
        padding: 8px;
        margin: 0;
        list-style: none;
    }
    .folder-breadcrumb > li {
        display: inline-block;
    }
    .folder-breadcrumb > li > a, .page-bar .page-breadcrumb > li > span {
        color: #888;
        font-size: 14px;
        text-shadow: none;
        text-decoration: none;
    }

    .folder-breadcrumb-item {
        display: inline-block;
        margin-left: 7px;
    }

    .folder-breadcrumb-item a{
        text-decoration: none;
    }
</style>