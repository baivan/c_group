var vmIndex = new Vue({
    el: '#index-container',
    data: {
        username: '',
        password: '',
        loading: [],
        alert_visible: false,
        alert_message: '',
        baseUrl: baseUrl
        //baseUrl: 'http://api.southwell.io/envirofit'
    },
    methods: {
        dismiss: function () {
            this.alert_visible = false;
        },
        login: function () {
            var required = [];
            if (!this.username) {
                required.push('username');

            }
            if (!this.password) {
                required.push('password');
            }

            if (required.length > 0) {
                this.alert_message = 'Fields required ---' + required.join();
                this.loading = [];
                this.alert_visible = true;
            } else {
                $('.btn').button('loading');
                this.alert_visible = false;

                var vm = this;
                axios.post(vm.baseUrl + '/login', {
                    username: vm.username,
                    password: vm.password
                }).then(function (response) {
                    console.log("Auth response: " + JSON.stringify(response));
                    var authenticated = response.data.authenticated;
                    $('.btn').button('reset');
                    if (authenticated) {
                        window.location.href = vm.baseUrl + "/members";
//                        window.location.reload(true);
                    } else {
                        vm.alert_message = response.data.error;
                        vm.loading = [];
                        vm.alert_visible = true;
                    }
                }).catch(function (error) {
                    vm.alert_message = 'Error while processing!';
                    $('.btn').button('reset');
                    vm.alert_visible = true;
                });
            }

        }
    }
});