<!DOCTYPE html>
<html>
<head>
	<title>Drafterbit File Browser</title>


	<?php $this->css(':bootstrap_css, @files/css/openfinder.css'); ?>
	<?php $this->css(':fontawesome', ':fontawesome'); ?>
	<?php echo  $this->block('css'); ?>

</head>
<script>
drafTerbit = {
	baseUrl: "<?php echo base_url() ?>",
	adminUrl: "<?php echo admin_url() ?>"
}
</script>
<body>

<div id="finder-container"></div>

<?php $this->js(':jquery, :bootstrap_js, :bootstrap_contextmenu, :jquery_form, @files/js/openfinder.js, @files/js/browser.js'); ?>
<?php echo $this->block('js'); ?>
</body>
</html>