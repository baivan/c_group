$(document).ready(function () {
    $("#payment-customer-select").select2({
        ajax: {
            url: vmSavings.baseUrl + "/users/contacts",
            dataType: 'json',
            type: "POST",
            data: function (params) {
                return {
                    search: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.fullName,
                            id: item.contactsID
                        };
                    })
                };
            },
            cache: true
        }
    });
});

$('[data-toggle="btns"] .btn').on('click', function (event) {
    var tabContentId = jQuery(this).attr("id");

    if (tabContentId === 'btn-unresolved') {
        vmSavings.isUnresolvedTabActive = true;
    } else {
        vmSavings.isUnresolvedTabActive = false;
    }

    var $this = $(this);
    $this.parent().find('.active').removeClass('active');
    $this.addClass('active');
});

$(function () {

    var start = moment();
    var end = moment();

    function cb(start, end) {
        if (vmSavings.isUnresolvedTabActive) {
            vmSavings.dateUnresolvedFilter(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));

        } else {
            vmSavings.dateFilter(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));

        }
        $('#udaterangepicker span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#daterangepicker span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    }

    $('#udaterangepicker').daterangepicker({
        startDate: start,
        endDate: end,
        opens: 'left',
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);

    $('#daterangepicker').daterangepicker({
        startDate: start,
        endDate: end,
        opens: 'left',
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    cb(start, end);

});

function reconcilePaymentModal() {
    $('#payment-reconcile').modal();
}

function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

var columns = [
    {
        name: '__sequence',
        title: '#',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'memberName',
        title: 'Given To',
        sortField: 'memberName',
        titleClass: 'table-header',
        dataClass: 'link-table-data',
        callback: 'customerName'

    },
    {
        name: 'memberName',
        title: 'Created By',
        sortField: 'memberName',
        titleClass: 'table-header',
        dataClass: 'link-table-data',
        callback: 'customerName'

    },
    {
        name: 'description',
        title: 'Description',
        sortField: 'description',
        titleClass: 'table-header',
        dataClass: 'link-table-data',
        callback: 'customerName'

    },
    {
        name: 'amount',
        title: 'Amount',
        sortField: 'amount',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'createdAt',
        title: 'Date',
        sortField: 'createdAt',
        titleClass: 'table-header',
        dataClass: 'table-data',
        callback:'createdAt'
    }
    /*,
    {
        name: '__component:update-loan-action',
        title: 'Actions',
        titleClass: 'table-options-header',
        dataClass: 'table-options-data'
    }*/
];

Vue.component('update-loan-action', {
    template: [
        '<div >',
          '<button id="edit-savings" data-loading-text="ACTIVATING..." class="btn btn-sm bg-primary margin-right-xs" @click="itemAction(\'update-savings\', rowData)"><i class="fa fa-edit"></i></button>',
          '<button id="delete-savings" data-loading-text="DELETING..." class="btn btn-sm btn-danger margin-right-xs" @click="itemAction(\'delete-savings\', rowData)"><i class="fa fa-times"></i></button>',
        '</div>'
    ].join(''),

    props: {
        activateText: 'Text',
        rowData: {
            type: Object,
            required: true
        }
    },
    methods: {
        itemAction: function (action, data) {
          ////  console.log("Updating a sale: " + JSON.stringify(data));
            if (action == 'update-savings') { 
               // vmSavings.expensesData = data;

                // if (data.status == 0) {
                //     updateLoanModal();
                // } else {
                //     payLoanModal();
                // }
               
            }
             else if (action == 'delete-savings') {
               // vmSavings.loanData = data;
                alertify.confirm('DELETE SALE', 'Are you sure you want to DELETE loan <strong>' + data.memberName + ' </strong> of KES. <strong>'+data.savingsAmount+' </strong> ?', function () {
                    vmSavings.deleteSavings(data.savingsId);
                }
                , function () {
                    return;
                });
            }
        }
    }
}); 



var vmSavings = new Vue({
    el: '#expenses-container',
    data: {
        unsavings_table_loading: false,
        savings_table_loading: false,
        initial_unresolved_loading: true,
        initial_resolved_loading: true,
        isUnresolvedTabActive: false,
        columns: columns,
        sortOrder: [{
                field: 'createdAt',
                direction: 'desc'
            }],
        unresolvedSortOrder: [{
                field: 'createdAt',
                direction: 'desc'
            }],
        multiSort: true,
        perPage: 10,
        unresolvedPerPage: 10,
        paginationComponent: 'vuetable-pagination',
        paginationInfoTemplate: 'Displaying {from} to {to} of {total} expenses',
        moreParams: ['filter=', 'start=', 'end=', 'isExport='],
        itemActions: [
            {
                name: 'reconcile',
                label: 'Reconcile',
                icon: 'fa fa-link',
                class: ' btn btn-success',
                extra: {'title': 'reconcile payment', 'data-toggle': "tooltip", 'data-placement': "left"}
            }
        ],
        search: '',
        search_unresolved: '',
        savings_amount:'',
        members:[],
        selected_member:'',
        total_expenses:'',
        expensesData:[],
        expense_amount:'',
        selected_member:'',
        expense_description:'',
        membersData:'',
       baseUrl: baseUrl
    },
    computed: {
        
    },
    methods: {
        customerName: function (value) {
            return '<strong>' + value + '</strong>';
        },
        createdAt: function (value) {
            if (!value) {
                return '-';
            } else {
                return moment(value).format('DD MMMM YYYY');
            }

        },
        dateFilter: function (start, end) {
            if (!this.initial_resolved_loading) {
                this.moreParams[1] = 'start=' + start;
                this.moreParams[2] = 'end=' + end;
                this.moreParams[3] = 'isExport='+true;
                this.$refs.vuetable_expenses.$nextTick(function () {
                    this.$dispatch('vuetable:refresh');
                });
            }
        },
        dateUnresolvedFilter: function (start, end) {
            if (!this.initial_unresolved_loading) {
                this.moreParams[1] = 'start=' + start;
                this.moreParams[2] = 'end=' + end;
                 this.moreParams[3] = 'isExport='+true;
                this.$refs.vuetable_unresolved.$nextTick(function () {
                    this.$dispatch('vuetable:refresh');
                });
            }
        },
        searchTransactions: function () {
            this.moreParams[0] = 'filter=' + this.search;
            this.$nextTick(function () {
                this.$refs.vuetable_expenses.$dispatch('vuetable:refresh');
            });
        },
        searchUnresolved: function () {
            this.moreParams[0] = 'filter=' + this.search_unresolved;
            this.$nextTick(function () {
                this.$refs.vuetable_unresolved.$dispatch('vuetable:refresh');
            });
        },
        getMembers: function () {
            this.members_select_loading = true;
            this.$http.get(this.baseUrl + '/members/membernames').then(function (response) {
                this.members_select_loading = false;
                var data = response.body;

                this.$nextTick(function () {
                    this.membersData = data;
                });

            }, function (error) {
                this.members_select_loading = false;
                this.membersData = [];
            });
        },
        addExpense: function () {
            if (!this.selected_member || !this.expense_amount || !this.expense_description) {
                alertify.notify('Missing required data', 'error', 5, function () {});
                return; 
            }

            $('#btn-expense-new').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/expenses/create', {
                memberId: vm.selected_member,
                amount: vm.expense_amount,
                description: vm.expense_description
            }).then(function (response) {
                var data = response.data;
              //  console.log("Response received: " + JSON.stringify(data));
                $('#btn-expense-new').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    vm.$refs.vuetable_expenses.$dispatch('vuetable:reload');
                    vm.selected_member = '';
                    vm.expense_description = '';
                    vm.expense_amount = '';
                    
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-expense-new').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        paginationConfig: function (componentName) {
            if (componentName == 'vuetable-pagination') {
                this.$broadcast('vuetable-pagination:set-options', {
                    wrapperClass: 'pagination',
                    icons: {first: '', prev: '', next: '', last: ''},
                    activeClass: 'active primary',
                    linkClass: 'btn btn-default',
                    pageClass: 'btn btn-default'
                });
            }
            if (componentName == 'vuetable-pagination-dropdown') {
                this.$broadcast('vuetable-pagination:set-options', {
                    wrapperClass: 'form-inline',
                    icons: {prev: 'glyphicon glyphicon-chevron-left', next: 'glyphicon glyphicon-chevron-right'},
                    dropdownClass: 'form-control'
                });
            }
        }
    },
    watch: {
        perPage: function (val, oldVal) {
            this.$refs.vuetable_expenses.$dispatch('vuetable:refresh');
        },
        unresolvedPerPage: function (val, oldVal) {
            this.$refs.vuetable_unresolved.$dispatch('vuetable:refresh');
        },
        paginationComponent: function (val, oldVal) {
            this.$broadcast('vuetable:load-success', this.$refs.vuetable.tablePagination);
            this.paginationConfig(this.paginationComponent);
        }
    },
    ready: function () {
        this.getMembers();
    },
    events: {
        'vuetable:row-changed': function (data) {
//          //  console.log('row-changed:', data.name);
        },
        'vuetable:row-clicked': function (data, event) {
//          //  console.log('row-clicked:', data.name);
        },
        'vuetable:cell-clicked': function (data, field, event) {
          //  console.log("Transaction Data: "+JSON.stringify(data));
            if (field.name == 'fullName' && (data.customerID || data.prospectsID)) {
                var customerID = 0;
                var prospectsID = 0;

                if (data.customerID) {
                    customerID = data.customerID;
                }

                if (data.prospectsID) {
                    prospectsID = data.prospectsID;
                }

                window.location.href = this.baseUrl + "/customers/customer_redirect/" + customerID + "/" + prospectsID + "/" + data.contactsID;
            }
        },
        'vuetable:action': function (action, data) {

            if (action === 'reconcile') {
              //  console.log("Transaction Data: " + JSON.stringify(data));
                this.transactionData = data;
                reconcilePaymentModal();
            }
        },
        'vuetable:loading': function () {
            if (this.initial_unresolved_loading) {
                this.unsavings_table_loading = true;
            }

            if (this.initial_resolved_loading) {
                this.savings_table_loading = true;
            }
        },
        'vuetable:load-success': function (response) {
            
            this.savings_table_loading = false;
            this.total_expenses = response.data.totalExpenesesAmount;
            this.expensesData = response.data;
        },
        'vuetable:load-error': function (response) {
            this.savings_table_loading = false;
            this.initial_resolved_loading = false;
            this.unsavings_table_loading = false;
            this.initial_unresolved_loading = false;
        }
    }
});