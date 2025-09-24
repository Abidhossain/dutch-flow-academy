v1.15.1
-------

- In `createContact` try to assign fixed customer_id if automatically generated id is not accepted.


v1.15.0
-------

- Add `getEstimatePdf`.


v1.14.0
-------

- Use pagination in `getProjects`.
- In `getTaxRates` use filter `active:true`.


v1.13.0
-------

- Add `getContactByCustomerId` method.


v1.12.0
-------

- Add `createContactPerson` method.


v1.11.0
-------

- Add `createSalesInvoiceAttachment` and `createEstimateAttachment` methods.


v1.10.0
-------

- Add `createSalesInvoiceNote`, `createRecurringSalesInvoiceNote`, `createEstimateNote` methods.


v1.9.0
------

- Add `updateContact` method.
- Add `createRecurringSalesInvoice` method.
- More details in debug log.


v1.8.3
------

- Fix: php warning in `getProducts` due to pagination.


v1.8.2
------

- Bugfix: passing an empty parameters array to sendSalesInvoice triggered HTTP response code 400.


v1.8.1
------

- Add extra argument to `getSalesInvoicePdf` method to support download of packing slip PDF.


v1.8
----

- Add `getProjects` method.


v1.7
----

- Add support for redirect responses.
- Add `getSalesInvoicePdf` method to obtain temporary invoice PDF download link.


v1.6
----

- Add `request_limit_reached` property to indicate that last request failed due to throttling.


v1.5
----

- Bugfix: also send body with DELETE requests (caused 400 errors in some cases).


v1.4
----

- Add method deleteSalesInvoice.


v1.3
----

- Add method createSalesInvoicePayment.


v1.2.3
------

- Allow contacts with empty company name if either firstname or lastname is filled.


v1.2.2
------

- Add pagination support for products.
- Add method deleteSalesInvoicePayment.


v1.2.1
------

- Retry failed cURL requests a couple of times.
- Increase request timeout to 15s.


v1.2
----

- Remove duplicate slashes in urls.
- Add debug log functionality.


v1.1
----

- Add Estimates endpoints
- Add Products endpoints
- Add query support for Contacts


v1.0.4
------

- Make request method public
- Add `getLedgerAccounts` method


v1.0.3
------

- Add functions to get/create purchase invoices


v1.0.2
------

- Bugfix: don't cast huge numbers to integer to prevent 32 bit trouble


v1.0.1
------

- Bugfix: unprintable error messages in some cases


v1.0
----

Initial version
