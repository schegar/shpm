<?php
session_set_cookie_params(0);
session_start();

require_once('php/lib.php');
require_once(PROJECT_ROOT.'php/connection.php');

$user = null;
if (isset($_SESSION['userid'])) {
    $user = R::load('user', $_SESSION['userid']);
}

$category = null;
if (isset($_GET['category'])) {
    $category = $_GET['category'];
} else {
    header("Location: index.php?category=index");
}

?>

<?php getSitePart('head'); ?>

<body>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/lib/jquery-2.2.3.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="js/lib/bootstrap.min.js"></script>
<script type="text/javascript" src="js/lib/bootstrap-editable.min.js"></script>


<?php if ($user): ?>

<?php getNav($category); ?>

<div class="container-fluid">
    <div class="row">
        <?php getSidebar($category); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php
                getPageHeader($category);
                if (strcmp($category, "index") !== 0) {
                    generateTable($category);
                }
            ?>
            <!--<button id="test" type="button" class="btn">Test</button>-->
        </div>
    </div>
</div>

    <script type="text/javascript" src="js/lib/datatables.min.js"></script>

    <script type="text/javascript" src="js/lib/bootstrap-editable.min.js"></script>
    <script type="text/javascript" src="js/lib/pwstrength-bootstrap.min.js"></script>
    <script type="text/javascript" src="js/lib/clipboard.min.js"></script>
    <script type="text/javascript" src="js/lib/notify.min.js"></script>

    <script type="text/javascript" src="js/password-generator.js"></script>

    <script type="text/javascript">
        var edit = [];
        var table;
        // TODO Edit Button smaller devices

        $(document).on('change', '.btn-file :file', function() {
            var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        $(document).ready( function () {

            $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
                var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;

                if( input.length ) {
                    input.val(log);
                } else {
                    if( log ) alert(log);
                }
            });

            $.fn.editable.defaults.mode = 'inline';

            $('.editAccount').on('click', function(e){
                e.preventDefault();
            });

            $.notify.defaults({
                autoHideDelay: 2000,
                position: "bottom right"
            });

            var clipboard = new Clipboard('.copy');

            clipboard.on('success', function(e) {
                //$(e.trigger).notify("Copied", "success");
                $.notify("Copied", "success");
            });

        });

        function isInArray(value, array) {
            return array.indexOf(value) > -1;
        }

    </script>

    <?php getSitePart('import-modal'); ?>
    <?php getSitePart('category-modal'); ?>
    <?php generateAddModal($category, ['name', 'username', 'password', 'comment']); ?>

<?php else: ?>

    <?php getSitePart('login-modal'); ?>

<?php endif;?>



</body>
</html>