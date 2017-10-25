$('[data-toggle="btns"] .btn').on('click', function (event) {
    var tabContentId = jQuery(this).attr("id");

    if (tabContentId === 'btn-prospect') {
        vmLoans.isProspectsTabActive = true;
    } else {
        vmLoans.isProspectsTabActive = false;
    }

    var $this = $(this);
    $this.parent().find('.active').removeClass('active');
    $this.addClass('active');
}); 

$(function () {

    var start = moment();
    var end = moment();

    function cb(start, end) {
        if (vmLoans.isProspectsTabActive) {
            vmLoans.dateProspectFilter(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));

        } else {
            vmLoans.dateFilter(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));

        }
        $('#pdaterangepicker span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#daterangepicker span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    }

    $('#pdaterangepicker').daterangepicker({
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

function customerUpdateModal() {
    $('#customer-update').modal();
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
        title: 'Member',
        sortField: 'memberName',
        titleClass: 'table-header',
        dataClass: 'link-table-data',
        callback: 'customerName'
    },
    {
        name: 'loanAmount',
        title: 'Principal',
        sortField: 'loanAmount',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'amountToPay',
        title: 'To Pay',
        sortField: 'amountToPay',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },

    {
        name: 'repaidAmount',
        title: 'Paid',
        sortField: 'repaidAmount',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'interestRate',
        title: 'Interest Rate',
        sortField: 'interestRate',
        titleClass: 'table-header', 
        dataClass: 'table-data'
    },
    {
        name: 'loanOfferDate',
        title: 'Issued',
        sortField: 'loanOfferDate',
        titleClass: 'table-header',
        dataClass: 'table-data',
        callback: 'createdAt'
    },
     {
        name: 'loanRepayDate',
        title: 'Due',
        sortField: 'loanRepayDate',
        titleClass: 'table-header',
        dataClass: 'table-data',
        callback: 'createdAt' 
    },
    {
        name: 'status',
        title: 'Status',
        sortField: 'status',
        titleClass: 'table-header',
        dataClass: 'table-data',
        callback: 'status' 
    },
    {
        name: '__component:update-loan-action',
        title: 'Actions',
        titleClass: 'table-options-header',
        dataClass: 'table-options-data'
    }
];


Vue.component('customer-source', {
    template: [
        '<span v-if="rowData.sourceName">{{rowData.sourceName}}</span>',
        '<span v-else>{{rowData.otherSource}}</span>'
    ].join(''),

    props: {
        activateText: 'Text',
        rowData: {
            type: Object,
            required: true
        }
    },
    methods: {}
});


Vue.component('update-loan-action', {
    template: [
        '<div >',
          '<button v-if="rowData.status == 0" id="btn-add-payment" data-loading-text="ACTIVATING..." class="btn btn-sm bg-primary margin-right-xs" @click="itemAction(\'award-loan\', rowData)"><i class="fa fa-check"></i></button>',
          '<button id="btn-add-payment" data-loading-text="ACTIVATING..." class="btn btn-sm bg-primary margin-right-xs" @click="itemAction(\'update-loan\', rowData)"><i class="fa fa-edit"></i></button>',
          '<button id="btn-delete-loan" data-loading-text="DELETING..." class="btn btn-sm btn-danger margin-right-xs" @click="itemAction(\'delete-loan\', rowData)"><i class="fa fa-times"></i></button>',
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
          //  console.log("Updating a sale: " + JSON.stringify(data));
            if (action == 'update-loan') { 
                vmLoans.loanData = data;

                if (data.status == 0) {
                    updateLoanModal();
                } else {
                    payLoanModal();
                }
               
            }
            else if (action == 'award-loan') { 
                 vmLoans.loanData = data;
                vmLoans.loanData = data;
                alertify.confirm('AWARD LOAN', 'Are you sure you want to Award <strong>' + data.memberName + ' </strong> loan of KES. <strong>'+data.loanAmount+' </strong> ?', function () {
                    vmLoans.awardLoan(data.loanId);
                }
                , function () {
                    return;
                });
            }
             else if (action == 'delete-loan') {
                vmLoans.loanData = data;
                alertify.confirm('DELETE SALE', 'Are you sure you want to DELETE loan <strong>' + data.memberName + ' </strong> of KES. <strong>'+data.loanAmount+' </strong> ?', function () {
                    vmLoans.deleteLoan(data.loanId);
                }
                , function () {
                    return;
                });
            }
        }
    }
});

function updateLoanModal() {
    $('#edit-loan').modal();
}
function payLoanModal() {
    $('#pay-loan').modal();
}

var vmLoans = new Vue({ 
    el: '#customers-container',
    data: {
        prospects_table_loading: false,
        customers_table_loading: false,
        initial_prospects_loading: true,
        initial_customers_loading: true,
        isProspectsTabActive: false,
        source_select_loading: false,
        columns: columns,
        sortOrder: [{
                field: 'loanId',
                direction: 'asc'
            }],
        multiSort: true,
        perPage: 10,
        prospectsPerPage: 10,
        paginationComponent: 'vuetable-pagination',
        paginationInfoTemplate: 'Displaying {from} to {to} of {total} Loans',
        prospectsPaginationInfoTemplate: 'Displaying {from} to {to} of {total} prospects',
        moreParams: ['filter=', 'start=', 'end=','isExport='],
        itemActions: [
            {
                name: 'delete-customer',
                label: '',
                icon: 'fa fa-times',
                class: 'btn btn-danger',
                extra: {'title': 'delete customer', 'data-toggle': "tooltip", 'data-placement': "left"}
            }
        ],
        prospectItemActions: [
            {
                name: 'delete-prospect',
                label: '',
                icon: 'fa fa-times',
                class: 'btn btn-danger',
                extra: {'title': 'delete customer', 'data-toggle': "tooltip", 'data-placement': "left"}
            }
        ],
        search: '',
        search_prospects: '',
        prospect_name: '',
        prospect_id_number: '',
        prospect_mobile_number: '',
        prospect_location_name: '',
        transaction_number: '',
        sale_amount: '',
        productsData: [],

        selected_customer: '',

        prospectSourcesData: '',
        selected_prospect_source: '',
        is_other_source: '',
        prospect_other_source: '',
        customersToExport: '',
        prospectsToExport: '',
        savings_amount:'',
        members:[],
        roles:[],
        selected_loan:'',
        loanData:'',
        loan_amount:'',
        repayment_amount:'',
        loan_due_date:'',
        total_loans:'',
        total_paid:'',
        user:'',
        is_from_shares:false,
        baseUrl:baseUrl 
    },
    computed: {
        prospects_tab: function () {
            return this.isProspectsTabActive;
        },
        header_label: function () {
            if (this.isProspectsTabActive) {
                return 'Prospects';
            } else {
                return 'Customers';
            }
        },
        prospectSources: function () {
            return this.prospectSourcesData;
        }
    },
    methods: {
        createdAt: function (value) {
            return moment(value).format('DD MMMM YYYY');
        },
        status: function (value) {
            if (value == 0) {
                return '<span>Not Awarded</span>';
            }
            else if(value == 1){
                return '<span>Awarded</span>';
            } else if(value==2) {
                return '<span>Fully Paid</span>';
            }
        },
        deleteCustomer: function (customerID, prospectsID) {

//            $('#btn-delete-sale').button('loading');

            var vm = this;
            axios.post(this.baseUrl + '/customers/delete', {
                customerID: customerID,
                prospectsID: prospectsID
            }).then(function (response) {
                var data = response.data;
//                console.log("Response received: " + JSON.stringify(data));
//                $('#btn-delete-sale').button('reset');

                if (data.status) {
                    if (customerID) {
                        vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                    } else {
                        vm.$refs.vuetable_prospects.$dispatch('vuetable:reload');
                    }

                    alertify.notify(data.success, 'success', 5, function () {});
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
//                $('#btn-delete-sale').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        editLoan: function () {
            $('#btn-edit-loan').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/loans/edit', {
                loanAmount: vm.loan_amount,
                loanRepaymentAmount:vm.repayment_amount,
                loanRepayDate:vm.loan_due_date,
                loanId: vm.loanData.loanId
            }).then(function (response) {
                var data = response.data;
                vm.loan_amount = "";
                vm.repayment_amount="";
                vm.loan_due_date="";
                vm.loanData="";
              //  console.log("Response received: " + JSON.stringify(data));

                $('#btn-edit-loan').button('reset');
                if (data.status) { 
                    alertify.notify(data.success, 'success', 5, function () {});
                    $('#edit-loan').modal('toggle');
                    vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#edit-loan').modal('toggle');
                $('#btn-edit-loan').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        payLoan: function () {
            $('#btn-pay-loan').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/loans/pay', {
                loanRepaymentAmount:vm.repayment_amount,
                loanId: vm.loanData.loanId,
                isFromShares: vm.is_from_shares
            }).then(function (response) {
                var data = response.data;
                vm.loan_amount = "";
                vm.repayment_amount="";
                vm.loan_due_date="";
                vm.loanData="";
                vm.is_from_shares=false;

                $('#btn-pay-loan').button('reset');
                if (data.status) { 
                    alertify.notify(data.success, 'success', 5, function () {});
                    $('#pay-loan').modal('toggle');
                    vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#pay-loan').modal('toggle');
                $('#btn-pay-loan').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        deleteLoan: function (loanId) {
            $('#btn-edit-loan').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/loans/delete', {
                loanId: loanId
            }).then(function (response) {
                var data = response.data;
              //  console.log("Response received: " + JSON.stringify(data));
                $('#btn-edit-loan').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    
                    vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        awardLoan: function (loanId) {
            $('#btn-edit-loan').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/loans/award', {
                loanId: loanId
            }).then(function (response) {
                var data = response.data;
              //  console.log("Response received: " + JSON.stringify(data));
                $('#btn-edit-loan').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    
                    vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                alertify.notify(error, 'error', 5, function () {});
            });

        }, 
        paginationConfig: function (componentName) {
            console.log('paginationConfig: ', componentName);
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
        },
        customerName: function (value) {
            return '<strong>' + value + '</strong>';
        },
        dateFilter: function (start, end) {
            if (!this.initial_customers_loading) {
                this.moreParams[1] = 'start=' + start;
                this.moreParams[2] = 'end=' + end;
                this.moreParams[3] = 'isExport='+true;
                this.$refs.vuetable_loans.$nextTick(function () {
                    this.$dispatch('vuetable:refresh');
                });
            }
        },
        dateProspectFilter: function (start, end) {
            if (!this.initial_prospects_loading) {
                this.moreParams[1] = 'start=' + start;
                this.moreParams[2] = 'end=' + end;
                this.moreParams[3] = 'isExport='+true;
                this.$refs.vuetable_prospects.$nextTick(function () {
                    this.$dispatch('vuetable:refresh');
                });
            }
        },
        searchCustomers: function () {
            this.moreParams[0] = 'filter=' + this.search;
            this.$refs.vuetable_loans.$nextTick(function () {
                this.$dispatch('vuetable:refresh');
            });
        },
        searchProspects: function () {
            this.moreParams[0] = 'filter=' + this.search_prospects;
            this.$refs.vuetable_prospects.$nextTick(function () {
                this.$dispatch('vuetable:refresh');
            });
        },
        getProspectSources: function () {
            this.source_select_loading = true;
            this.$http.get(this.baseUrl + '/customers/prospectsources').then(function (response) {
                this.source_select_loading = false;
                var data = response.body;
//                console.log("Dispositions: " + JSON.stringify(data));
                this.$nextTick(function () {
                    this.roles = data;
                });

            }, function (error) {
                this.source_select_loading = false;
                this.prospectSources = [];
            });
        },
        exportCustomers: function () {
            var data = [];

            for (var count = 0; count < this.customersToExport.length; count++) {

                var item = {
                    CUSTOMER_ID: this.customersToExport[count].customerID,
                    CUSTOMER: this.customersToExport[count].fullName,
                    CUSTOMER_MOBILE: this.customersToExport[count].workMobile,
                    NATIONAL_ID: this.customersToExport[count].nationalIdNumber,
                    CRB_RATE:this.customersToExport[count].crbCheckStatus,
                    LOCATION: this.customersToExport[count].location,
                    SINCE: this.customersToExport[count].createdAt
                };

                data.push(item);
            }

            exportDate = moment().format('DD_MMMM_YYYY_h:mm');

//            console.log("Exporting data: " + JSON.stringify(this.dataToExport));
            JSONToCSVConvertor(data, 'customers_' + exportDate, 1);
        },
        exportProspects: function () { 
            var data = [];

            for (var count = 0; count < this.prospectsToExport.length; count++) {

                var item = { 
                    PROSPECT_ID: this.prospectsToExport[count].prospectsID,
                    CUSTOMER: this.prospectsToExport[count].fullName,
                    CUSTOMER_MOBILE: this.prospectsToExport[count].workMobile,
                    NATIONAL_ID: this.prospectsToExport[count].nationalIdNumber,
                    LOCATION: this.prospectsToExport[count].location,
                    SOURCE: this.prospectsToExport[count].sourceName ? this.prospectsToExport[count].sourceName : this.prospectsToExport[count].otherSource,
                    SINCE: this.prospectsToExport[count].createdAt
                };

                data.push(item);
            }

            exportDate = moment().format('DD_MMMM_YYYY_h:mm');

//            console.log("Exporting data: " + JSON.stringify(this.dataToExport));
            JSONToCSVConvertor(data, 'prospects_' + exportDate, 1);
        }
    },
    watch: {
        perPage: function (val, oldVal) {
            this.$refs.vuetable_loans.$dispatch('vuetable:refresh');
        },
        prospectsPerPage: function (val, oldVal) {
            this.$refs.vuetable_prospects.$dispatch('vuetable:refresh');
        },
        selected_prospect_source: function (val) {
            if (val) {
                this.is_other_source = false;
                this.prospect_other_source = '';
            }
        },
        is_other_source: function (val) {
//            console.log("Other source change: " + val);
            if (val) {
                this.selected_prospect_source = '';
            }
        },
        paginationComponent: function (val, oldVal) {
            this.$broadcast('vuetable:load-success', this.$refs.vuetable.tablePagination);
            this.paginationConfig(this.paginationComponent);
        }
    },
    ready: function () {
        this.getProspectSources();
    },
    events: {
        'vuetable:row-changed': function (data) {
//            console.log('row-changed:', data.name);
        },
        'vuetable:row-clicked': function (data, event) {
//            console.log('row-clicked:', data.name);
        },
        'vuetable:cell-clicked': function (data, field, event) {
//            console.log("Clicking this..." + JSON.stringify(data));
//            return;

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

//            if (field.name == 'fullName' && data.prospectsID) {
//                window.location.href = this.baseUrl + "/customers/prospect_redirect/" + data.customerID + "/" + data.prospectsID + "/" + data.contactsID;
//            }
        },
        'vuetable:action': function (action, data) {
//            console.log('vuetable:action', action, data);
            if (action == 'delete-customer') {
                alertify.confirm('DELETE CUSTOMER', 'Are you sure you want to DELETE <strong>' + data.fullName + '</strong>?', function () {
                    vmLoans.deleteCustomer(data.customerID, 0);
                }
                , function () {
                    return;
                });
            }
            if (action == 'delete-prospect') {
                alertify.confirm('DELETE PROSPECT', 'Are you sure you want to DELETE <strong>' + data.fullName + '</strong>?', function () {
                    vmLoans.deleteCustomer(0, data.prospectsID);
                }
                , function () {
                    return;
                });
            }
        },
        'vuetable:loading': function () {
            if (this.initial_prospects_loading) {
                this.prospects_table_loading = true;
            }

            if (this.initial_customers_loading) {
                this.customers_table_loading = true;
            }
        },
        'vuetable:load-success': function (response) {
            this.loan_table_loading = false;
            console.log("testung "+JSON.stringify(response));
            var repaymentSummary = response.data.repaymentSummary;
            this.total_loans = repaymentSummary.amountToPay;
            this.total_paid = repaymentSummary.repaidAmount;
        },
        'vuetable:load-error': function (response) {
            this.table_loading = false;
            if (response.status == 400) {
            } else {
            }
        }
    }
});