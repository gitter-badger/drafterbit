<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title ?> | Drafterbit</title>
        <!-- Core CSS - Include with every page -->
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400' rel='stylesheet' type='text/css'>

        <?php $this->css(':fontawesome', ':fontawesome'); ?>
        <?php $this->css('
          :bootstrap_css,
          :toastr_css,
          @system/css/overrides-bootstrap.css,
          @system/css/overrides-toastr.css,
          @system/css/overrides-datatables.css,
          @system/css/style.css
        ') ?>
        <?php echo $this->block('css'); ?>

        <script>
        (function(){
          drafTerbit = {
            baseUrl: "<?php echo base_url() ?>",
            adminUrl: "<?php echo admin_url() ?>",
            csrfToken: "<?php echo csrf_token() ?>",

            //replace datatable search box;
            replaceDTSearch: function(dt) {
              $('.dataTables_filter').remove();


              $(document).on('keydown', "input[type=search]", function(e){
                var code = e.keyCode || e.which;
                if (code == 13) {
                  e.preventDefault();
                }}
              );

              //search filter
              $(document).on('keyup', "input[type=search]", function(e){

                var val = $(this).val();
                dt.api().search($(this).val()).draw();
                
              });
            }
          }
        })();
        </script>
    </head>

    <body>

            <?php echo $this->render('@system/partials/nav'); ?>

            <div class="page-wrapper">
                <div class="container row-header-container">
                  <div class="row row-header">
                      <div class="col-lg-12" style="margin-bottom:10px;">
                          <h2> <?php echo $title; ?> </h2>
                      </div>
                  </div>
              </div>
                <?php echo $this->block('content'); ?>
            </div>
            <!-- /#page-wrapper -->

            <!-- footer -->
            <?php echo $this->render('@system/partials/footer'); ?>

        <!-- script -->
        <?php $this->js(':jquery, :bootstrap_js, :toastr_js, @system/js/layout.js, @system/js/app.js'); ?>
        <?php echo $this->block('js'); ?>
        
        <?php if(isset($messages)): ?>
        <script>
                toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "positionClass": "toast-top-center",
                  "onclick": null,
                  "showDuration": "500",
                  "hideDuration": "100",
                  "timeOut": "5000",
                  "extendedTimeOut": "10000000",
                  "showEasing": "linear",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                }
                <?php foreach( $messages as $message ): ?>
                    msg = "<?php echo $this->escape($message['text'], 'js'); ?>";
                    toastr.<?php echo $message['type'] ?>(msg, "<?php echo $this->escape($message['title'], 'js'); ?>");
                <?php endforeach; ?>
        </script>
        <?php endif;?>
</body>
</html>