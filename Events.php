<?php

/*
 * PortaOne Billing JSON API wrapper
 * API docs: https://www.portaone.com/docs/PortaBilling_API.html
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

/**
 * Class for array of event and corresponding methods to call.
 * Basically, method name is camel-cased event name. If it starts with IP and DID -
 * it is not decapitalised.
 * 
 * Events as for Nov 6, 2019, Release 80
 */

namespace Portaone;

abstract class Events {

    const LIST = array(
        'Accessibility/Deleted' => 'accessibilityDeleted',
        'Accessibility/Inserted' => 'accessibilityInserted',
        'AccessPolicy/Changed' => 'accessPolicyChanged',
        'AccessPolicy/Phase/Changed' => 'accessPolicyPhaseChanged',
        'AccessPolicy/Phase/Deleted' => 'accessPolicyPhaseDeleted',
        'AccessPolicy/Phase/New' => 'accessPolicyPhaseNew',
        'Account/AccessPolicy/Changed' => 'accountAccessPolicyChanged',
        'Account/ActivationDate/Changed' => 'accountActivationDateChanged',
        'Account/Alias/Delete' => 'accountAliasDelete',
        'Account/AvailableFundsAppear' => 'accountAvailableFundsAppear',
        'Account/BalanceChanged' => 'accountBalanceChanged',
        'Account/Blocked' => 'accountBlocked',
        'Account/CustomField/Changed' => 'accountCustomFieldChanged',
        'Account/Discount/Changed' => 'accountDiscountChanged',
        'Account/ExpirationDate/Changed' => 'accountExpirationDateChanged',
        'Account/FollowMe/Changed' => 'accountFollowMeChanged',
        'Account/FollowMeNumber/Changed' => 'accountFollowMeNumberChanged',
        'Account/FollowMeNumber/Deleted' => 'accountFollowMeNumberDeleted',
        'Account/FollowMeNumber/Inserted' => 'accountFollowMeNumberInserted',
        'Account/Id/Changed' => 'accountIdChanged',
        'Account/IPDeviceAssignment' => 'accountIPDeviceAssignment',
        'Account/New' => 'accountNew',
        'Account/Password/Changed' => 'accountPasswordChanged',
        'Account/ProductAddon/Changed' => 'accountProductAddonChanged',
        'Account/ProductAddon/Deleted' => 'accountProductAddonDeleted',
        'Account/ProductAddon/Inserted' => 'accountProductAddonInserted',
        'Account/Product/Changed' => 'accountProductChanged',
        'Account/ServiceAttribute/Changed' => 'accountServiceAttributeChanged',
        'Account/ServiceFlags/Changed' => 'accountServiceFlagsChanged',
        'Account/Service/Msg/QuotaExceeded' => 'accountServiceMsgQuotaExceeded',
        'Account/Service/Netaccess/QuotaExceeded' => 'accountServiceNetaccessQuotaExceeded',
        'Account/ServicePassword/Changed' => 'accountServicePasswordChanged',
        'Account/Service/QuotaAvailable' => 'accountServiceQuotaAvailable',
        'Account/Service/QuotaExceeded' => 'accountServiceQuotaExceeded',
        'Account/SIMCardAssignment' => 'accountSIMCardAssignment',
        'Account/Status/Changed' => 'accountStatusChanged',
        'Account/Status/Closed' => 'accountStatusClosed',
        'Account/Status/Exported' => 'accountStatusExported',
        'Account/Status/Imported' => 'accountStatusImported',
        'Account/Status/Suspend' => 'accountStatusSuspend',
        'Account/Status/Unsuspend' => 'accountStatusUnsuspend',
        'Account/Unblocked' => 'accountUnblocked',
        'Account/ZeroAvailableFunds' => 'accountZeroAvailableFunds',
        'Connection/Changed' => 'connectionChanged',
        'Connection/Deleted' => 'connectionDeleted',
        'Connection/New' => 'connectionNew',
        'Customer/AvailableFundsAppear' => 'customerAvailableFundsAppear',
        'Customer/BalanceChanged' => 'customerBalanceChanged',
        'Customer/Blocked' => 'customerBlocked',
        'Customer/CustomField/Changed' => 'customerCustomFieldChanged',
        'Customer/Name/Changed' => 'customerNameChanged',
        'Customer/New' => 'customerNew',
        'Customer/ServiceAttribute/Changed' => 'customerServiceAttributeChanged',
        'Customer/ServiceFlags/Changed' => 'customerServiceFlagsChanged',
        'CustomerSite/ServiceAttribute/Changed' => 'customerSiteServiceAttributeChanged',
        'Customer/Status/Activated' => 'customerStatusActivated',
        'Customer/Status/Changed' => 'customerStatusChanged',
        'Customer/Status/Closed' => 'customerStatusClosed',
        'Customer/Status/Deactivated' => 'customerStatusDeactivated',
        'Customer/Status/Exported' => 'customerStatusExported',
        'Customer/Status/Imported' => 'customerStatusImported',
        'Customer/Status/Suspend' => 'customerStatusSuspend',
        'Customer/Status/Unsuspend' => 'customerStatusUnsuspend',
        'Customer/Unblocked' => 'customerUnblocked',
        'Customer/ZeroAvailableFunds' => 'customerZeroAvailableFunds',
        'CustomField/Changed' => 'customFieldChanged',
        'Custom/Jasper/CapReached' => 'customJasperCapReached',
        'DID/Deleted' => 'dIDDeleted',
        'DID/New' => 'dIDNew',
        'DID/Status/Activated' => 'DIDStatusActivated',
        'DID/Status/Assigned' => 'DIDStatusAssigned',
        'DID/Status/Canceled' => 'DIDStatusCanceled',
        'DID/Status/Moved' => 'DIDStatusMoved',
        'DID/Status/Unassigned' => 'DIDStatusUnassigned',
        'Invoice/Adjustments/Changed' => 'invoiceAdjustmentsChanged',
        'Invoice/AmountPaid/Changed' => 'invoiceAmountPaidChanged',
        'Invoice/New' => 'invoiceNew',
        'Invoice/Status/Changed' => 'invoiceStatusChanged',
        'IPDeviceProfile/New' => 'IPDeviceProfileNew',
        'PaymentTransaction/New' => 'paymentTransactionNew',
        'PaymentTransaction/ResultCode/Changed' => 'paymentTransactionResultCodeChanged',
        'PaymentTransaction/Status/Changed' => 'paymentTransactionStatusChanged',
        'Product/AccessPolicy/Changed' => 'productAccessPolicyChanged',
        'Product/Discount/Changed' => 'productDiscountChanged',
        'Product/ServiceAttribute/Changed' => 'productServiceAttributeChanged',
        'ServiceAttribute/Changed' => 'serviceAttributeChanged',
        'Subscriber/Address/Changed' => 'subscriberAddressChanged',
        'Subscriber/ContactInfo/Changed' => 'subscriberContactInfoChanged',
        'Subscriber/Name/Changed' => 'subscriberNameChanged',
    );

}
