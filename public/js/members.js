var columns = [
    {
        name: '__sequence',
        title: '#',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'memberName',
        title: 'Name',
        sortField: 'memberName',
        titleClass: 'table-header',
        dataClass: 'link-table-data',
        callback: 'agentName'
    },
    {
        name: 'memberPhoneNumber',
        title: 'Phone',
        sortField: 'memberPhoneNumber',
        titleClass: 'table-header',
        dataClass: 'table-data'

    },
    {
        name: 'totalSavings',
        title: 'Total Savings',
        sortField: 'totalSavings',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'repaidAmount',
        title: 'Loan Repayment',
        sortField: 'repaidAmount',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: '__component:loan-pending',
        title: 'Total Pending',
        sortField: 'pendingLoans',
        titleClass: 'table-header',
        dataClass: 'table-data'
    }
];

var savingColumns = [
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
        dataClass: 'table-data'

    },
    {
        name: 'savingsAmount',
        title: 'Amount',
        sortField: 'savingsAmount',
        titleClass: 'table-header',
        dataClass: 'table-data'
    },
    {
        name: 'createdAt',
        title: 'Date',
        sortField: 'createdAt',
        titleClass: 'table-header',
        dataClass: 'table-data', 
        callback: 'createdAt'
    }
];

var loanColumns = [
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
    }
];

Vue.component('loan-pending', { 
    template: [
        '<div v-if="rowData.amountToPay">',
        '<span>{{rowData.amountToPay - rowData.repaidAmount}}</span>',
        '</div>'
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

var vmAgents = new Vue({
    el: '#agents-container', 
    data: {
        roles_select_loading: false,
        agents_table_loading: true,
        columns: columns,
        savingColumns: savingColumns,
        loanColumns: loanColumns,
        sortOrder: [{
                field: 'userID',
                direction: 'asc'
            }],
        saleSortOrder: [{
                field: 'savingsId',
                direction: 'desc'
            }],
        itemSortOrder: [{
                field: 'loanId',
                direction: 'desc'
            }],
        multiSort: true,
        perPage: 10,
        paginationComponent: 'vuetable-pagination',
        paginationInfoTemplate: 'Displaying {from} to {to} of {total} members',
        salePaginationInfoTemplate: 'Displaying {from} to {to} of {total} savings',
        itemPaginationInfoTemplate: 'Displaying {from} to {to} of {total} loans',
        moreParams: ['filter='],
        itemActions: [],
        selected_role:'',
        roles:[],
        search: '',
        full_names: '',
        phone_number: '',
        email_address: '',
        id_number: '',
        user_location: '',
        selected_agent_type: '',
        agentTypesData: [{agentTypeID: 1, agentTypeName: 'DSR'}, {agentTypeID: 2, agentTypeName: 'ISA'}],
        selected_agent: '',
        agentData: '',
        agentsToExport:'',
        savings_amount:'',
        selected_agent_status: false,
        loan_amount:'',
        loan_due_date:'',
        membersData:[],
        selected_dest_member:'',
        transfer_amount:'',
       // baseUrl: 'http://api.southwell.io/envirofit'
        baseUrl: baseUrl
    },
    computed: {
        agentTypes: function () {
            return this.agentTypesData;
        },
        is_agent_selected: function () {
            if (!this.selected_agent) {
                return false;
            } else {
                return true;
            }
        },
        agent_sales_url: function () {
//            return this.baseUrl + '/agents/sales/2';
            return this.baseUrl + '/members/savings/' + this.selected_agent.memberId;
        },
        agent_items_url: function () {
            return this.baseUrl + '/members/loans/' + this.selected_agent.memberId;
        }
    },
    methods: {
        agentNumber: function (value) {
            return value.toUpperCase();
        },
        agentName: function (value) {
            return '<strong>' + value + '</strong>';
        },
        createdAt: function (value) {
            if (!value) {
                return '-';
            } else {
                return moment(value).format('DD MMMM YYYY');
            }

        },status: function (value) {
            if (value == 0) {
                return '<span>Not Awarded</span>';
            }
            else if(value == 1){
                return '<span>Awarded</span>';
            } else if(value==2) {
                return '<span>Fully Paid</span>';
            }
        },
        statusIndicator: function (value) {
            if (value == 0) {
                return '<span class="bg-warning padding-xs">assigned</span>';
            } else if (value == 1) {
                return '<span class="bg-info padding-xs padding-left-lg padding-right-lg">received</span>';
            } else if (value == 2) {
                return '<span class="bg-danger padding-xs padding-left-sm padding-right-sm">item sold out</span>';
            } else {
                return '<span class="bg-default padding-xs">returned</span>';
            }
        },
        productItems: function (value) {
            var cell = '<div>';
            for (var count = 0; count < value.length; count++) {
                cell += '<div><strong>' + value[count].serialNumber + '</strong> - ' + value[count].productName + '</div>';
            }
            cell += '</div>';
            return cell;
        },
        depositAmount: function (value) {
            var amount = 0;
            for (var count = 0; count < value.length; count++) {
                amount += Number(value[count].depositAmount);
            }
            return amount;
        },
        addMember: function () {
            if (!this.full_names || !this.phone_number || !this.id_number || !this.selected_role) {
                alertify.notify('Missing required data', 'error', 5, function () {});
                return; 
            }

            $('#btn-agent').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/members/create', {
                fullNames: vm.full_names,
                phoneNumber: vm.phone_number,
                idNumber: vm.id_number, 
                roleID: vm.selected_role
            }).then(function (response) {
                var data = response.data;
               // console.log("Response received: " + JSON.stringify(data));
                $('#btn-agent').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    vm.$refs.vuetable_agents.$dispatch('vuetable:reload');
                    vm.full_names = '';
                    vm.phone_number = '';
                    vm.email_address = '';
                    vm.id_number = '';
                    vm.user_location = '';
                    vm.selected_role = '';
//                    $('#agent-new').modal('toggle');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-agent').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        transferSaving: function () {
            if (!this.transfer_amount && !this.selected_dest_member ) {
                alertify.notify('Missing required data', 'error', 5, function () {});
                return;
            }
            $('#btn-transfer-savings').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/savings/transfer', {
                amount: vm.transfer_amount,
                destMemberId:vm.selected_dest_member,
                originMemberId: vm.selected_agent.memberId
            }).then(function (response) { 
                var data = response.data;
              // // console.log("Response received: " + JSON.stringify(data));
                $('#btn-transfer-savings').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    $('#transfer-savings').modal('toggle');
                    vm.$refs.vuetable_savings.$dispatch('vuetable:reload');
                    vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                    vm.$refs.vuetable_agents.$dispatch('vuetable:reload');

                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-transfer-savings').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
         addSaving: function () {
            if (!this.savings_amount ) {
                alertify.notify('Missing required data', 'error', 5, function () {});
                return;
            }

            $('#btn-new-savings').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/savings/create', {
                savingAmount: vm.savings_amount,
                memberId: vm.selected_agent.memberId,
            }).then(function (response) {
                var data = response.data;
              // // console.log("Response received: " + JSON.stringify(data));
                $('#btn-new-savings').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    $('#new-savings').modal('toggle');
                    vm.$refs.vuetable_savings.$dispatch('vuetable:reload');
                    vm.$refs.vuetable_agents.$dispatch('vuetable:reload');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-new-savings').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        createLoan: function () {
            if (!this.loan_amount || !this.loan_due_date ) {
                alertify.notify('Missing required data', 'error', 5, function () {});
                return;
            }

            $('#btn-new-loan').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/loans/create', {
                loanAmount: vm.loan_amount,
                loanRepayDate:vm.loan_due_date,
                memberId: vm.selected_agent.memberId, 
            }).then(function (response) {
                var data = response.data;
              // // console.log("Response received: " + JSON.stringify(data));
                $('#btn-new-loan').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    $('#new-loan').modal('toggle');
                    vm.$refs.vuetable_loans.$dispatch('vuetable:reload');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-new-loan').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },

        agentActivator: function (status) {
            var vm = this;
            axios.get(vm.baseUrl + '/users/activate/' + status + '/' + this.selected_agent.userID)
                    .then(function (response) {
                        $('#btn-activate').button('reset');
                        $('#btn-deactivate').button('reset');
                        if (response.status == 200 && response.data.status) {
                            vm.selected_agent = '';
                            if (status == 1) {
                                alertify.notify('agent activated successfully', 'success', 5, function () {});
                                vm.agentData.status = 1;

                            } else {
                                alertify.notify('agent deactivated successfully', 'success', 5, function () {});
                                vm.agentData.status = 0;
                            }
                            vm.$refs.vuetable_agents.$dispatch('vuetable:reload');
                            vm.selected_agent = vm.agentData;
                        } else {
                            alertify.notify(response.data.error, 'error', 5, function () {});
                        }
                    }).catch(function (error) {
                $('#btn-activate').button('reset');
                $('#btn-deactivate').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });
        },
        activateAgent: function () {
            var status = 0;

            if (this.selected_agent.status == 0) {
                status = 1;
                alertify.confirm('Activate Agent', 'Are you sure you want to activate?', function () {
                    $('#btn-activate').button('loading');
                    vmAgents.agentActivator(status);
                }
                , function () {
                    return;
                });

            } else {
                status = 0;
                alertify.confirm('Deactivate Agent', 'Are you sure you want to deactivate?', function () {
                    $('#btn-deactivate').button('loading');
                    vmAgents.agentActivator(status);
                }
                , function () {
                    return;
                });
            }
        },
        searchAgents: function () {
            this.moreParams[0] = 'filter=' + this.search;
            this.$refs.vuetable_agents.$dispatch('vuetable:refresh');
        },
        updateAgent: function () {
//           // console.log("Selected Agent Data: "+ JSON.stringify(this.selected_agent));

            if (!this.selected_agent.fullName || !this.selected_agent.nationalIdNumber ||
                    !this.selected_agent.workMobile || !this.selected_agent.location || !this.selected_agent.workEmail || !this.selected_agent.agentType) {
                alertify.notify('Missing required data', 'error', 5, function () {});
                return;
            }

            $('#btn-agent-update').button('loading');
            var vm = this;
            axios.post(vm.baseUrl + '/users/update', vm.selected_agent).then(function (response) {
                var data = response.data;
//               // console.log("Response received: " + JSON.stringify(data));
                $('#btn-agent-update').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    $('#agent-update').modal('hide');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-agent-update').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });

        },
        getRoles: function () {
            this.roles_select_loading = true;
            this.$http.get(this.baseUrl + '/members/roles').then(function (response) {
                this.roles_select_loading = false;
                var data = response.body;
               //// console.log("Dispositions: " + JSON.stringify(data));

                this.$nextTick(function () {
                    this.roles = data;
                });

            }, function (error) {
                this.roles_select_loading = false;
                this.roles = [];
            });
        },
        sendMessage: function(){
            $('#btn-send').button('loading');
            $('#send-message').button('loading');

            var vm = this;
            axios.post(vm.baseUrl + '/members/sendmessage', vm.message).then(function (response) {
                var data = response.data;
               // console.log("Response received: " + JSON.stringify(data));
                $('#btn-send').button('reset');
                $('#send-message').button('reset');
                if (data.status) {
                    alertify.notify(data.success, 'success', 5, function () {});
                    vm.message = '';
                   
                    $('#sms-send').modal('hide');
                } else {
                    alertify.notify(data.error, 'error', 5, function () {});
                }
            }).catch(function (error) {
                $('#btn-send').button('reset');
                $('#send-message').button('reset');
                alertify.notify(error, 'error', 5, function () {});
            });
        },
        exportAgents: function () {
            var data = [];

            for (var count = 0; count < this.agentsToExport.length; count++) {
                var status ="ACTIVE";
                  if(this.agentsToExport[count].status > 1 ){
                         status ="ACTIVE";
                  }
                  else if(this.agentsToExport[count].status == 0){
                        status ="DEACTIVATED";
                  }

                var item = {
                    Agent : this.agentsToExport[count].fullName,
                    Phone : this.agentsToExport[count].workMobile,
                    Agent_Number  : this.agentsToExport[count].agentNumber,
                    Region : this.agentsToExport[count].location,
                    Status :status,
                    Since: this.agentsToExport[count].createdAt
                };

                data.push(item);
            }

            exportDate = moment().format('DD_MMMM_YYYY_h:mm');

//           // console.log("Exporting data: " + JSON.stringify(this.dataToExport));
            JSONToCSVConvertor(data, 'agents_' + exportDate, 1);
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
            this.$broadcast('vuetable:refresh');
        },
        paginationComponent: function (val, oldVal) {
            this.$broadcast('vuetable:load-success', this.$refs.vuetable.tablePagination);
            this.paginationConfig(this.paginationComponent);
        },
        selected_agent: function (val) {
            if (val.status == 1) {
                this.selected_agent_status = true;
            } else {
                this.selected_agent_status = false;
            }
            this.$refs.vuetable_savings.$dispatch('vuetable:refresh');
            this.$refs.vuetable_loans.$dispatch('vuetable:refresh');
        }
    },
    ready: function () {
        this.getRoles();
    },
    events: {
        'vuetable:row-changed': function (data) {
//           // console.log('row-changed:', data.name);
        },
        'vuetable:row-clicked': function (data, event) {},
        'vuetable:cell-clicked': function (data, field, event) {
            if (field.name != 'memberName') {
                return;
            }
            this.selected_agent = data;
            this.agentData = data;

        },
        'vuetable:action': function (action, data) {
//           // console.log('vuetable:action', action, data);
        },
        'vuetable:load-success': function (response) {
            this.agents_table_loading = false;

            if(response.data.type=='members'){
               // console.log("Members "+JSON.stringify(response.data));
               this.membersData = response.data.data;
            }
           // this.agentsToExport = response.data.exportAgents;
            
           ////console.log("Vuetable data: " + JSON.stringify(response));
        },
        'vuetable:load-error': function (response) {
            if (response.status == 400) {
                alertify.error(response.data);
            } else {
                alertify.error(response.data);
            }
        }
    }
});