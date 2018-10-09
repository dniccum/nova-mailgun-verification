<template>
    <div>
        <div v-if="loading">
            <p><em>Retrieving domain status...</em></p>
        </div>

        <div class="pt-2 pb-2" v-if="successfulResponse && !loading && !notAdded">
            <h3 class="pb-2">Status</h3>
            <div v-if="!isDisabled">

                <div v-if="!verified">
                    <p class="text-90">
                        <span class="inline-block rounded-full w-2 h-2 mr-1 bg-danger"></span>
                        <span>Unverified</span>
                    </p>
                </div>
                <!--
                <div v-if="!verified && partiallyVerified">
                    <p class="text-90">
                        <span class="inline-block rounded-full w-2 h-2 mr-1 bg-warning"></span>
                        <span>Partially Verified - <span class="text-italic">Your sending records have been verified, but it is recommended that you add your sending MX records as well.</span> </span>
                    </p>
                </div>
                -->

                <div v-if="verified">
                    <p class="text-90">
                        <span class="inline-block rounded-full w-2 h-2 mr-1 bg-success"></span>
                        <span>Verified</span>
                    </p>
                </div>

            </div>
            <p class="text-90" v-if="isDisabled">
                <strong>This domain has been disabled.</strong> Please refer to your Mailgun account dashboard for more information.
            </p>

            <div class="pt-4" v-if="!verified">
                <h4 class="pb-2">Records to be added</h4>
                <p class="mb-4">Add the following DNS records to your domain in order for it to be verified. It may take a little while for the records to propagate and/or take effect.</p>
                <div class="bg-20 border border-40 px-4 py-3 rounded relative mb-2" v-for="dnsRecord in recordsToAdd">
                    <div class="flex mb-2">
                        <div style="width: 100px;">
                            <p><strong>Type:</strong></p>
                        </div>
                        <div class="w-full ml-6">
                            <p class="break-words">{{ dnsRecord.record_type }}</p>
                        </div>
                    </div>
                    <div class="flex mb-2">
                        <div style="width: 100px;">
                            <p><strong>Host/Name:</strong></p>
                        </div>
                        <div class="w-full ml-6">
                            <p class="break-words">{{ dnsRecord.name }}</p>
                        </div>
                    </div>
                    <div class="flex mb-4 mt-4">
                        <div style="width: 100px;">
                            <p><strong>Value:</strong></p>
                        </div>
                        <div class="w-full ml-6">
                            <input type="text"
                                   class="form-control form-input form-input-bordered w-full"
                                   readonly
                                   :value="dnsRecord.value" />
                        </div>
                    </div>
                    <div class="flex mb-2">
                        <div style="width: 100px;">
                            <p><strong>Valid:</strong></p>
                        </div>
                        <div class="w-full ml-6">
                            <p class="break-words">
                                <span class="text-danger font-bold" v-if="dnsRecord.valid === 'unknown' || dnsRecord.valid === 'invalid'">{{ dnsRecord.valid }}</span>
                                <span class="text-success font-bold" v-if="dnsRecord.valid === 'valid'">{{ dnsRecord.valid }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!successfulResponse && !loading">
            <div class="bg-red-lightest border border-red-light text-red-dark px-4 py-3 rounded relative"
                 style="background-color: #FCEBEA; border-color: #EF5753;"
                 role="alert">
                <strong class="font-bold" style="color: #CC1F1A">Error!</strong>
                <span class="block sm:inline" style="color: #CC1F1A">
                    {{ error }}
                </span>
            </div>
        </div>

        <div class="pt-2 pb-2" v-if="notAdded && !loading">
            <h3 class="pb-2">Domain not added.</h3>
            <p class="pb-4">This domain has not been added to your Mailgun account. Click the button below to add the domain for verification.</p>
            <button type="button"
                    class="text-white font-bold py-2 px-4 rounded"
                    :class="addingDomain ? 'bg-70' : 'bg-primary hover:bg-primary-dark'"
                    @click="addDomain"
                    :disabled="addingDomain">
                {{ addingDomain ? 'Adding domain...' : 'Add Domain' }}
            </button>
        </div>
    </div>
</template>

<script>
    import Axios from 'axios';

    export default {
        props: ['resourceName', 'resourceId', 'field'],

        mounted() {
            this.getStatus();
        },
        data: () => {
            return {
                loading: true,
                successfulResponse: false,
                error: '',
                verified: false,
                partiallyVerified: false,
                isDisabled: false,
                notAdded: false,
                addingDomain: false,
                recordsToAdd: []
            }
        },
        methods: {
            compileRequest() {
                let requestData = {
                    id: this.resourceId,
                    model: this.resourceName
                };

                if (this.field.attribute) {
                    requestData['attribute'] = this.field.attribute;
                }

                return requestData;
            },

            getStatus() {
                let vm = this;

                Axios.get('/nova-vendor/mailgun-domain-verification/domain', {
                    params: this.compileRequest()
                }).then(response => {
                    let responseBody = response.data.domain.http_response_body;
                    let domain = responseBody.domain;
                    let status = true;

                    vm.successfulResponse = true;
                    vm.isDisabled = domain.is_disabled;
                    vm.recordsToAdd = responseBody.sending_dns_records;

                    for (var i = 0; i < vm.recordsToAdd.length; i++) {
                        if (vm.recordsToAdd[i].valid !== 'valid') {
                            status = false;
                            break;
                        }
                    }
                    vm.verified = status;
                }).catch(error => {
                    let data = error.response.data;
                    let exception = data.exception;

                    switch (exception) {
                        case 'Mailgun\\Connection\\Exceptions\\InvalidCredentials':
                            vm.error = 'Your Mailgun credentials are not correct. Please ensure that your Mailgun Key and SMTP password are set.'
                            break;
                        case 'Mailgun\\Connection\\Exceptions\\MissingEndpoint':
                            vm.successfulResponse = true;
                            vm.notAdded = true;
                            break;
                    }

                    if (!vm.successfulResponse && vm.error === '') {
                        vm.error = data.message;
                    }
                }).finally(() => {
                    vm.loading = false;
                })
            },

            addDomain() {
                let vm = this;

                vm.addingDomain = true;

                Axios.post('/nova-vendor/mailgun-domain-verification/domain', this.compileRequest()).then(response => {
                    let responseBody = response.data.domain.http_response_body;
                    let domain = responseBody.domain;

                    vm.successfulResponse = true;
                    vm.notAdded = false;
                    vm.isDisabled = domain.is_disabled;
                    vm.recordsToAdd = responseBody.sending_dns_records;

                    this.$toasted.show('Domain successfully added.', { type: 'success' })
                }).catch(error => {
                    console.error(error.response);

                    let data = error.response.data;
                    let exception = data.exception;

                    switch (exception) {
                        case 'Mailgun\\Connection\\Exceptions\\InvalidCredentials':
                            vm.error = 'Your Mailgun credentials are not correct. Please ensure that your Mailgun Key and SMTP password are set.';
                            break;
                        default:
                            vm.error = 'Something has happened.';
                            break;
                    }

                    this.$toasted.show('There was a problem adding your domain.', { type: 'error' })
                }).finally(() => {
                    vm.addingDomain = false;
                })
            }
        }
    }
</script>
