<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['accounts/in-hand'] = 'accounts/in_hand';
$route['accounts/bank'] = 'accounts/bank';
$route['accounts/credits'] = 'accounts/credits';
$route['accounts/loan'] = 'accounts/loan';

$route['default_controller'] = 'login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'login/index';
$route['login/validate'] = 'login/login_validation';
$route['logout'] = 'login/logout';
$route['profile'] = 'systemUser/profile';

$route['home'] = 'home/index';
$route['dashboard'] = 'home/index';

$route['category-manage'] = 'itemCategory/manageCategory';
$route['saveCategory'] = 'itemCategory/saveCategory';
$route['updateCategory'] = 'itemCategory/updateCategory';
$route['deleteCategory/(:num)'] = 'itemCategory/deleteCategory/$1';

$route['brand-manage'] = 'itemBrand/manageBrand';
$route['saveBrand'] = 'itemBrand/saveBrand';
$route['updateBrand'] = 'itemBrand/updateBrand';
$route['deleteBrand/(:num)'] = 'itemBrand/deleteBrand/$1';
$route['undoDeleteBrand/(:num)'] = 'itemBrand/undoDeleteBrand/$1'; 

$route['vehicle-category-manage'] = 'vehicleCategory/manageVehicleCategory';
$route['saveVehicleCategory'] = 'vehicleCategory/saveVehicleCategory';
$route['updateVehicleCategory'] = 'vehicleCategory/updateVehicleCategory';
$route['deleteVehicleCategory/(:num)'] = 'vehicleCategory/deleteVehicleCategory/$1';
$route['undoDeleteVehicleCategory/(:num)'] = 'vehicleCategory/undoDeleteVehicleCategory/$1';

$route['vehicle-brand-manage'] = 'vehicleBrand/manageVehicleBrand';
$route['saveVehicleBrand'] = 'vehicleBrand/saveVehicleBrand';
$route['updateVehicleBrand'] = 'vehicleBrand/updateVehicleBrand';
$route['deleteVehicleBrand/(:num)'] = 'vehicleBrand/deleteVehicleBrand/$1';
$route['undoDeleteVehicleBrand/(:num)'] = 'vehicleBrand/undoDeleteVehicleBrand/$1';  

$route['expenses-category-manage'] = 'expensesCategory/manageExpensesCategory';
$route['saveExpensesCategory'] = 'expensesCategory/saveExpensesCategory';
$route['updateExpensesCategory'] = 'expensesCategory/updateExpensesCategory';
$route['deleteExpensesCategory/(:num)'] = 'expensesCategory/deleteExpensesCategory/$1';
$route['undoDeleteExpensesCategory/(:num)'] = 'expensesCategory/undoDeleteExpensesCategory/$1';

$route['employee-section-manage'] = 'employeeSection/manageEmployeeSection';
$route['saveEmployeeSection'] = 'employeeSection/saveEmployeeSection';
$route['updateEmployeeSection'] = 'employeeSection/updateEmployeeSection';
$route['deleteEmployeeSection/(:num)'] = 'employeeSection/deleteEmployeeSection/$1';
$route['undoDeleteEmployeeSection/(:num)'] = 'employeeSection/undoDeleteEmployeeSection/$1';

$route['service-item-category-manage'] = 'serviceItemCategory/manageServiceItemCategory';
$route['saveServiceItemCategory'] = 'serviceItemCategory/saveServiceItemCategory';
$route['updateServiceItemCategory'] = 'serviceItemCategory/updateServiceItemCategory';
$route['deleteServiceItemCategory/(:num)'] = 'serviceItemCategory/deleteServiceItemCategory/$1';
$route['undoDeleteServiceItemCategory/(:num)'] = 'serviceItemCategory/undoDeleteServiceItemCategory/$1';

$route['system-role-manage'] = 'systemRole/manageSystemRole';
$route['saveSystemRole'] = 'systemRole/saveSystemRole';
$route['updateSystemRole'] = 'systemRole/updateSystemRole';
$route['deleteSystemRole/(:num)'] = 'systemRole/deleteSystemRole/$1';
$route['undoDeleteSystemRole/(:num)'] = 'systemRole/undoDeleteSystemRole/$1';   

$route['system-user-manage'] = 'systemUser/manageSystemUser';
$route['employeeList'] = 'systemUser/employeeList';
$route['saveSystemUser'] = 'systemUser/saveSystemUser';
$route['updateSystemUser'] = 'systemUser/updateSystemUser';
$route['deleteSystemUser'] = 'systemUser/deleteSystemUser';
$route['toggleUserStatus'] = 'systemUser/toggleUserStatus';
$route['setTemporaryPassword'] = 'systemUser/setTemporaryPassword';

// Attendance routes
$route['attendance'] = 'attendance/index';
$route['attendance/bulk'] = 'attendance/bulk';
$route['attendance/getMonthlyGrid'] = 'attendance/getMonthlyGrid';
$route['attendance/saveMonthlyGrid'] = 'attendance/saveMonthlyGrid';
$route['attendance/getAttendanceData'] = 'attendance/getAttendanceData';
$route['attendance/saveAttendance'] = 'attendance/saveAttendance';
$route['attendance/getDaysInMonth'] = 'attendance/getDaysInMonth';
$route['attendance/test'] = 'attendance/test';

// Advance routes
$route['advance'] = 'advance/index';
$route['advance/getAdvanceData'] = 'advance/getAdvanceData';
$route['advance/test'] = 'advance/test';

$route['systemRole/getRolePermissions/(:num)'] = 'systemRole/getRolePermissions/$1';

$route['customers'] = 'customer/manageCustomer';
$route['saveCustomer'] = 'customer/saveCustomer';
$route['getCustomerById'] = 'customer/getCustomerById';
$route['updateCustomer'] = 'customer/updateCustomer';

$route['customer/get_districts'] = 'customer/get_districts';
$route['customer/get_cities'] = 'customer/get_cities';

$route['supplier'] = 'supplier/manageSupplier';
$route['saveSupplier'] = 'supplier/saveSupplier';
$route['getSupplierById'] = 'supplier/getSupplierById';
$route['updateSupplier'] = 'supplier/updateSupplier';

$route['supplier/get_districts'] = 'supplier/get_districts';
$route['supplier/get_cities'] = 'supplier/get_cities';

$route['vehicles'] = 'vehicle/manageVehicle';
$route['saveVehicle'] = 'vehicle/saveVehicle';
$route['updateVehicle'] = 'vehicle/updateVehicle';

$route['products'] = 'products/index';

$route['services-items-manage'] = 'services/serviceItemsManage';
$route['saveServiceItem'] = 'services/saveServiceItem';

$route['services-packages-manage'] = 'services/servicePackagesManage';
$route['saveServicePackage'] = 'services/saveServicePackage';
$route['services/loadServicePackages'] = 'services/loadServicePackages';
$route['services/getServicePackage'] = 'services/getServicePackage';
$route['services/deleteServicePackage'] = 'services/deleteServicePackage';
$route['services/loadServiceItemsForPackage'] = 'services/loadServiceItemsForPackage';
$route['services/loadPackageProducts'] = 'services/loadPackageProducts';


$route['purchase-manage'] = 'purchase/purchaseManage';
$route['purchase-history'] = 'purchase/loadPurchaseHistory';
$route['purchaseditem-history'] = 'purchase/loadPurchasedItemHistory';
$route['addPurchaseOrderItem'] = 'purchase/addPurchaseOrderItem';
$route['internalBill'] = 'internalBill/internalBill';
$route['internalBillList'] = 'internalBill/internalBillList';
$route['internalBill/getBillDetails'] = 'internalBill/getBillDetails';
$route['internalBill/deleteBill'] = 'internalBill/deleteBill';
$route['internalBill/loadBillItems'] = 'internalBill/loadBillItems';
$route['internalBill/deleteCartItem'] = 'internalBill/deleteCartItem';
$route['internalBill/saveCompletedBill'] = 'internalBill/saveCompletedBill';
$route['internalBill/addProductToBill'] = 'internalBill/addProductToBill';
$route['internalBill/loadServiceProductsFilterListByProduct'] = 'internalBill/loadServiceProductsFilterListByProduct';
$route['internal_bill/print_receipt/(:num)'] = 'internalBill/print_receipt/$1';
$route['purchase-history-manage'] = 'purchase/purchaseHistoryManage';
$route['purchase/updatePurchase'] = 'purchase/updatePurchase';
$route['purchase/loadEditPoItems'] = 'purchase/loadEditPoItems';
$route['purchase/updatePoItem'] = 'purchase/updatePoItem';
$route['purchase/deletePoItem'] = 'purchase/deletePoItem';

// PO Payment System Routes
$route['purchase/loadPoPayments'] = 'purchase/loadPoPayments';
$route['purchase/addPoPayment'] = 'purchase/addPoPayment';
$route['purchase/deletePoPayment'] = 'purchase/deletePoPayment';
$route['purchase/loadPurchaseHistory'] = 'purchase/loadPurchaseHistory';
$route['purchase/getPurchaseHistoryDetails'] = 'purchase/getPurchaseHistoryDetails';
$route['purchase/getPurchasedItemDetails'] = 'purchase/getPurchasedItemDetails';
$route['purchase/updateSellPrice'] = 'purchase/updateSellPrice';
$route['purchase/writeOffProductStock'] = 'purchase/writeOffProductStock';
$route['purchase/writeOffBatchStock']   = 'purchase/writeOffBatchStock';

$route['services-manage'] = 'job/servicesManage';
$route['print-job/(:any)'] = 'job/printInvoice/$1';
$route['print-job-mini/(:any)'] = 'job/printMiniInvoice/$1';
$route['invoices'] = 'job/invoices';

//quick bill
$route['quick-bill'] = 'quickBill/quickBill';
$route['quickBill/deleteCartItem'] = 'quickBill/deleteCartItem';
$route['quickBill/loadBillItems'] = 'quickBill/loadBillItems';
$route['quickBill/saveCompletedBill'] = 'quickBill/saveCompletedBill';
$route['quickBill/deleteQuickBill'] = 'quickBill/deleteQuickBill';
$route['quickBill/getQuickBillForEdit/(:num)'] = 'quickBill/getQuickBillForEdit/$1';
$route['quickBill/updateQuickBill'] = 'quickBill/updateQuickBill';
$route['quickBill/getBillPaymentHistory'] = 'quickBill/getBillPaymentHistory';
$route['quickBill/updatePayment'] = 'quickBill/updatePayment';
$route['quickBill/deletePayment'] = 'quickBill/deletePayment';
$route['quickBill/createPayment'] = 'quickBill/createPayment';
$route['quickBill/getReturnData'] = 'quickBill/getReturnData';
$route['quick_bill/print_receipt/(:num)'] = 'QuickBill/print_receipt/$1';
$route['quick-bill-list'] = 'quickBill/quickBillList';
$route['quickBill/loadServiceProductsFilterListByProduct'] = 'quickBill/loadServiceProductsFilterListByProduct';
$route['quickbill/saveWalkInCustomer'] = 'quickBill/saveWalkInCustomer';


//payments
$route['payments'] = 'payments/paymentManage';

//Expenses
$route['expenses-manage'] = 'expenses/expensesManage';
$route['expenses/getNextVoucherNumber'] = 'expenses/getNextVoucherNumber';

//stock
$route['item-stock'] = 'purchase/stockManage';
$route['stock-update'] = 'StockUpdate/index';

//cron
$route['cron/update-stock'] = 'CronController/update_stock';

// reports
$route['reports/daily'] = 'reports/daily';
$route['reports/daily/data'] = 'reports/dailyData';
$route['reports/daily/print'] = 'reports/dailyPrint';
$route['reports/daily/pdf'] = 'reports/dailyPdf';
$route['reports/daily/detailed'] = 'reports/dailyDetailed';
$route['reports/monthly'] = 'reports/monthly';
$route['reports/monthly/data'] = 'reports/monthlyData';
$route['reports/monthly/print'] = 'reports/monthlyPrint';
$route['reports/monthlyPdf'] = 'reports/monthlyPdf';
$route['reports/monthly/detailed'] = 'reports/monthlyDetailed';
$route['reports/stock'] = 'reports/stock';
$route['reports/stockData'] = 'reports/stockData';
$route['reports/stockPdf'] = 'reports/stockPdf';
$route['reports/expenses'] = 'reports/expenses';
$route['reports/expensesData'] = 'reports/expensesData';
$route['reports/expensesPdf'] = 'reports/expensesPdf';
$route['reports/getEmployees'] = 'reports/getEmployees';
$route['reports/getCategories'] = 'reports/getCategories';

// SMS tester
$route['sms-tester'] = 'sms/tester';
$route['sms/send'] = 'sms/send';

