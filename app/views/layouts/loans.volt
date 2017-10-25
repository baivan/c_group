{% do assets.addJs('vendor/vue/vue-1.0.28.min.js') %}
{% do assets.addJs('vendor/vue-table/vue-resource.min.js') %}
{% do assets.addJs('vendor/vue-table/vue-table.min.js') %}
{% if is_customer %}
	{% do assets.addJs('js/customer.js') %}
{% else %}
	{% do assets.addJs('js/loans.js') %}
{% endif %}

{{content()}}