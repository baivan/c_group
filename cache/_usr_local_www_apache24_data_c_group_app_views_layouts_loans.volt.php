<?php $this->assets->addJs('vendor/vue/vue-1.0.28.min.js'); ?>
<?php $this->assets->addJs('vendor/vue-table/vue-resource.min.js'); ?>
<?php $this->assets->addJs('vendor/vue-table/vue-table.min.js'); ?>
<?php if ($is_customer) { ?>
	<?php $this->assets->addJs('js/customer.js'); ?>
<?php } else { ?>
	<?php $this->assets->addJs('js/loans.js'); ?>
<?php } ?>

<?= $this->getContent() ?>