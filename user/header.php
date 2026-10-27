
<link rel="stylesheet" href="../../links/bootstrap-5.3/css/bootstrap.min.css">
<link rel="stylesheet" href="../../links/sidebar/style.css">
<link rel='stylesheet' href='../../links/icon/remixicon.css'>
<link rel='stylesheet' href='../../links/dist/css/css-pro-layout.css'>
<link rel='stylesheet' href='../../links/font/montserrat.css'>
<link rel="stylesheet" href="../../links/datatable/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="../../links/datatable/css/buttons.dataTables.min.css">
<link href="../../links/select2/select2.min.css" rel="stylesheet" />
<link href="../../links/select2/select2-bootstrap.min.css" rel="stylesheet" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    /* Data table */
    .active>.page-link,
    .page-link.active {
        background-color: rgb(140, 136, 102);
        border-color: rgb(140, 136, 102);
    }

    .page-link,
    div.dataTables_wrapper div.dataTables_filter input {
        font-size: .6rem;
        border-radius: 0;
        color: rgb(140, 136, 102);
    }

    .dataTables_length,
    .dataTables_info {
        padding-left: 12px;
    }

    .dataTables_filter,
    .dataTables_paginate {
        padding-right: 12px;
    }

    div.dataTables_wrapper div.dataTables_length select {
        font-size: .6rem;
        border-radius: 0;
    }

    .pagination {
        --bs-pagination-border-radius: 0;
    }

    .table-bordered {
        border-left-color: rgba(0, 0, 0, 0);
        border-right-color: rgba(0, 0, 0, 0);
    }


    /* Card */
    .card {
        border-radius: 0;
        border-color: rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #fff;
        padding: 12px 12px 12px 12px;
        border-color: rgba(0, 0, 0, 0.1);
    }

    .card-header h6 {
        line-height: 0;
        align-self: center;
        padding: 0;
        margin: 0;
    }

    .card-body {
        background-color: #fff;
        padding: 12px 0 12px 0;
        border-color: rgba(0, 0, 0, 0.1);
    }

    /* Btn Add */
    .btn.btn-add {
        border: 1px solid lightgray;
        border-radius: 0;
        font-size: .6rem;

    }

    .btn i {
        font-size: .75rem;
    }

    .btn.btn-add:hover {
        background-color: lightgray;
    }


     /* Btn action */
     .btn.btn-action {
        border: 1px solid lightgray;
        border-radius: 0;
        font-size: .6rem;
    }

    .btn.btn-action:hover {
        background-color: lightgray;
    }

    /* Badge */
    .badge.In.progress {
        background-color: #FFF3CD;
        color: #664D03;
        border: solid 1px #664D0320;
        font-size: .6rem;
    }

    .badge.Closed {
        background-color: #D1E7DD;
        color: #0A3622;
        border: solid 1px #0A362220;
        font-size: .6rem;
    }

    .badge.Open {
        background-color: #f2f2f2;
        color: #0A3622;
        border: solid 1px #0A362220;
        font-size: .6rem;
    }

    /* Modal */
    .modal-content {
        border-radius: 2px;
        border: 0;
    }

    .modal-footer .btn, .save-profile .btn.btn-primary{
        border-radius: 0;
        font-size: .7rem;
    }

    .modal-footer .btn.btn-primary, .save-profile .btn.btn-primary {
        background-color: #092A2F;
        border: 1px solid #092A2F;
    }

    .modal-footer .btn.btn-primary:hover, .save-profile .btn.btn-primary:hover{
        background-color: #8C8866;
        border: 1px solid #8C8866;
    }

    .modal-footer .btn.btn-secondary {
        background-color: #fff;
        border: 1px solid lightgray;
        color: #000;
    }

    .modal-footer .btn.btn-secondary:hover {
        background-color: lightgray;
    }

    .modal-header h1 {
        font-size: .9rem;
    }

    .modal-body {
        font-size: .8rem;
    }

    .modal-body label {
        display: flex;
        height: 100%;
        align-items: center;
    }

    input:focus,textarea:focus {
        outline: 0;
        border-color: transparent !important;
    }


    .form-control.form-control-sm.focus-ring {
        border-radius: 5px;
    }

    .form-control.form-control-sm.focus-ring:focus {
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        background-color: #F0F2F5;
        transition: .2s all ease-in-out;
    }



    .form-control-sm {
        padding: .0rem .5rem;
        font-size: .8rem;
    }

    .upload-files-btn {
        font-size: .75rem !important;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 100px !important;
        padding: 4px 18px;
        transition: .2s all ease-in-out;
    }

    .upload-files-btn:hover {
        background-color: #F0F2F5;
        transition: .2s all ease-in-out;
    }

    .upload-file-btn {
        font-size: .75rem !important;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 100px !important;
        padding: 4px 18px;
        transition: .2s all ease-in-out;
    }

    .upload-file-btn:hover {
        background-color: #F0F2F5;
        transition: .2s all ease-in-out;
    }

    .comment-upload-file-btn {
        font-size: .75rem !important;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 100px !important;
        padding: 4px 18px;
        transition: .2s all ease-in-out;
    }

    .comment-upload-file-btn:hover {
        background-color: #F0F2F5;
        transition: .2s all ease-in-out;
    }

    .project-comment-upload-file-btn {
        font-size: .75rem !important;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 100px !important;
        padding: 4px 18px;
        transition: .2s all ease-in-out;
    }

    .project-comment-upload-file-btn:hover {
        background-color: #F0F2F5;
        transition: .2s all ease-in-out;
    }

    .file-preview {
        position: relative;
    }

    .file-preview:hover img {
        opacity: .8;
    }

    .file-preview:hover .remove-file {
        color: white;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .file-preview img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        margin-right: 5px;
    }

    
    .comment-file-preview {
        position: relative;
    }

    .comment-file-preview:hover img {
        opacity: .8;
    }

    .comment-file-preview:hover .remove-file {
        color: white;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .comment-file-preview img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        margin-right: 5px;
    }

    .project-comment-file-preview {
        position: relative;
    }

    .project-comment-file-preview:hover img {
        opacity: .8;
    }

    .project-comment-file-preview:hover .remove-file {
        color: white;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .project-comment-file-preview img {
        margin-bottom:6px;
        width: 100px;
        height: 100px;
        object-fit: cover;
        margin-right: 5px;
    }



    .remove-file {
        cursor: pointer;
        position: absolute;
        top: 30%;
        right: 5px;
        color: white;
        background-color: rgba(0, 0, 0, 0.3);
        padding: 0px 3px;
        font-size: 2rem;
        z-index: 1;
    }


    .select2-container--default .select2-search--dropdown .select2-search__field{
        border: 1px solid rgb(140, 136, 102)!important;
    }


    .form-control.form-control-sm.comment{
        background-color: rgba(242,244,246,0.5);
        border-radius:0; 
        border:0; 
        padding:6px 12px
    }
    .form-control.form-control-sm.comment:focus{
        background-color: rgb(242,244,246);
        border:0!important; 
    }

    .upload-file-btn.comment{
        padding:3px 9px;
        background-color:#fff
    }
    .upload-file-btn.comment:hover{
        padding:3px 9px;
        background-color:#D9DBDD;
    }

    .comment-upload-file-btn.comment{
        padding:3px 9px;
        background-color:#fff
    }
    .comment-upload-file-btn.comment:hover{
        padding:3px 9px;
        background-color:#D9DBDD;
    }

    
    .project-comment-upload-file-btn.comment{
        padding:3px 9px;
        background-color:#fff
    }
    .project-comment-upload-file-btn.comment:hover{
        padding:3px 9px;
        background-color:#D9DBDD;
    }

    td a{
        color:#0A3622;
    }
    td a:hover{
        color:#8C8866;
    }


    ul.dropdown-menu.dropdown-menu-end
    {
        border-radius: 0;
        font-size:.7rem;
        padding:0;
    }
    
</style>
