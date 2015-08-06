<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
   <title>Demo Rechnung</title>
   <style type="text/css">

      @font-face {
         font-family: 'Open Sans';
         font-style: normal;
         font-weight: 300;
         src: local('Open Sans Light'), local('OpenSans-Light'), url(http://fonts.gstatic.com/s/opensans/v13/DXI1ORHCpsQm3Vp6mXoaTZS3E-kSBmtLoNJPDtbj2Pk.ttf) format('truetype');
      }
      @font-face {
         font-family: 'Open Sans';
         font-style: normal;
         font-weight: 400;
         src: local('Open Sans'), local('OpenSans'), url(http://fonts.gstatic.com/s/opensans/v13/cJZKeOuBrn4kERxqtaUH3SZ2oysoEQEeKwjgmXLRnTc.ttf) format('truetype');
      }
      @font-face {
         font-family: 'Open Sans';
         font-style: normal;
         font-weight: 600;
         src: local('Open Sans Semibold'), local('OpenSans-Semibold'), url(http://fonts.gstatic.com/s/opensans/v13/MTP_ySUJH_bn48VBG8sNSpS3E-kSBmtLoNJPDtbj2Pk.ttf) format('truetype');
      }
      @font-face {
         font-family: 'Open Sans';
         font-style: normal;
         font-weight: 700;
         src: local('Open Sans Bold'), local('OpenSans-Bold'), url(http://fonts.gstatic.com/s/opensans/v13/k3k702ZOKiLJc3WVjuplzJS3E-kSBmtLoNJPDtbj2Pk.ttf) format('truetype');
      }

      @page {
         margin: 20px 0 150px 0;
      }

      body {
         /*font-family: sans-serif;*/

         font-family: "Open Sans", sans-serif, Helvetica;

         margin: 100px 0 0.5cm 0;
         text-align: justify;
      }

      .offset-left {
         margin-left: 40px;
      }

      .offset-right {
         margin-right: 40px;
      }

      #header,
      #footer {
         position: fixed;
         left: 0;
         right: 0;
         color: #aaa;
         font-size: 0.9em;
      }

      #header {
         position: fixed;
         top: 0;
      }

      #footer {
         /*bottom: 130px;*/
         bottom: 0;
      }

      #footer .page-info {
         text-align: center;
         font-size: 10px;
         border-bottom: 0.1pt solid #aaa;
         padding-bottom: 10px;

      }

      #header table,
      #footer table {
         width: 100%;
         border-collapse: collapse;
         border: none;
      }

      #header td,
      #footer td {
         padding: 0;
         width: 50%;
      }

      .page-number {
         text-align: center;
      }

      .page-number:before {
         content: "Page " counter(page);
      }

      hr {
         page-break-after: always;
         border: 0;
      }

      .container-to {
         margin-left: 40px;
      }

      .text-date {
         color: #999;
      }

      .branding {
         width: 100%;
         height: 100px;
         margin-right: 50px;
         margin-top: 20px;
         text-align: right;
      }

      .box-colored {
         background: #359EE0;
         width: 100%;
         padding: 30px;
         text-align: left;
         color: #fff;
         margin-left: 150px;
         font-size: 12px;
         line-height: 14px;
      }

      .text-docinfo {
         width: 100%;
         padding: 10px 0 0 30px;
         text-align: left;
         margin-left: 150px;
         font-size: 11px;
         line-height: 13px;
         color: #707070;
      }

      h1, h2 {
         color: #359EE0;
         text-transform: uppercase;
         font-size: 22px;
         margin: 0;
         padding: 0;
      }

      h2 {
         font-size: 18px;
      }

      .text-invoice-to {
         color: #707070;
         line-height: 15px;
      }

      .spacer {
         width: 100%;
         height: 40px;
      }

      .table-invoice {
         width: 100%;
         padding: 0;
         margin: 0;
         font-family: sans-serif;
      }

      .table-invoice tr, .table-invoice tr td {
         margin: 0;
         padding: 0;
         border: 0;
      }

      .table-invoice tr:first-child td {
         font-weight: bold;
         text-transform: uppercase;
         color: #474747;
         font-size: 11px;
         padding: 5px 5px;
      }

      .table-invoice tr, .table-invoice tr td:first-child {
         padding-left: 40px;
      }

      .table-invoice tr, .table-invoice tr td:last-child {
         padding-right: 40px;
      }

      .table-invoice tr td {
         background: #fff;
         padding: 20px 5px;
         font-size: 11px;
         color: #6B6B6B;
      }

      .table-invoice tr:nth-child(even) td {
         background: #FAFAFA;
         border-top: 1px solid #efefef;

      }

      .table-invoice tr.footer td {
         background: #F5F5F5;
         text-align: right;
      }

      .table-invoice tr.footer td table tr td {
         margin: 0;
         padding: 3px 0;
         border: 0;
      }

      .text-color {
         color: #359EE0 !important;
      }

      .text-meta {
         margin-left: 40px;
         margin-right: 40px;
         padding-bottom: 20px;
         padding-top: 20px;
         font-size: 12px;
         line-height: 18px;
         font-family: sans-serif;
         color: #7A7A7A;
      }

      .text-thanks {
         margin: 10px 0 0 0;
         text-align: center;
         background: #359EE0;
         color: #fff;
         padding: 15px 0;
         font-size: 18px;
         line-height: 1;
         font-weight: 100;
         text-transform: uppercase;
      }

      .table-footer {
         width: 100%;
         padding: 0;
         margin: 0;
         font-size: 9px;
         line-height: 14px;
         font-family: sans-serif;
      }

      .table-footer tr:first-child td {
         font-weight: bold;
         text-transform: uppercase;
         color: #474747;
         font-size: 10px;
         padding: 20px 0 5px 0;
         margin-bottom: 4px !important;
      }

      .col-6 {
         width: 50%;
         float: left;
      }

      .clear {
         clear: both;
      }

      .todo {
         color: red !important;
      }

      .text-left { text-align: left }
      .text-center { text-align: center }
      .text-right { text-align: right }

      .pagenum:before { content: "" counter(page); }

      /* Color Themes */

      /* Shopwardemo Orange */
      /*.box-colored { background: #E44A15; }*/
      /*h1, h2 { color: #E44A15; }*/
      /*.text-color { color: #E44A15 !important; }*/
      /*.text-thanks { background: #E44A15; }*/

      /* Pink */
      /*.box-colored { background: #D86F7F; }*/
      /*h1, h2 { color: #D86F7F; }*/
      /*.text-color { color: #D86F7F !important; }*/
      /*.text-thanks { background: #D86F7F; }*/

      /* Small Blue */
      /*.box-colored { background: #4399B6; }*/
      /*h1, h2 { color: #4399B6; }*/
      /*.text-color { color: #4399B6 !important; }*/
      /*.text-thanks { background: #4399B6; }*/

      /* Dark blue */
      /*.box-colored { background: #354459; }*/
      /*h1, h2 { color: #354459; }*/
      /*.text-color { color: #354459 !important; }*/
      /*.text-thanks { background: #354459; }*/

      /* Brown */
      /*.box-colored { background: #5A323C; }*/
      /*h1, h2 { color: #5A323C; }*/
      /*.text-color { color: #5A323C !important; }*/
      /*.text-thanks { background: #5A323C; }*/

   </style>

</head>

<body>

{debug}

<div id="footer">

   <div class="page-info">
      Seite <span class="pagenum"></span>
   </div>

   <table class="table-footer" cellpadding="0" cellspacing="0" style="margin: 20px 50px 0 30px;">
      <tr>
         <td class="todo">Demo Shop GmbH</td>
         <td class="todo">Bankverbindung</td>
         <td class="todo">AGB</td>
         <td class="todo">Geschäftsführung</td>
      </tr>
      <tr>
         <td class="todo" valign="top">
            Steuer-Nr: DE 900 400 200 <br>
            UST-ID: DE 100 400 200 <br>
            Finanzamt Musterstadt
         </td>
         <td class="todo" valign="top">
            Sparkasse Musterstadt <br>
            IBAN: D3004005001003442353 <br>
            BIC: WELADEXX
         </td>
         <td class="todo" valign="top">
            Gerichtsstand ist Musterstadt <br>
            Erfüllungsort Musterstadt <br>
            Siehe auch mustershop.de/agb
         </td>
         <td class="todo" valign="top">
            Frank Mustermann <br>
            Sabine Musterfrau <br>
            Franz Musterkomisch
         </td>
      </tr>
   </table>

</div>

<div id="header">

   <div class="row">
      <div class="col-6">
         <div class="offset-left" style="margin-top: 30px;">
            <span class="text-date">{$documentDate|date_format:"d.m.Y"}</span>
         </div>
      </div>
      <div class="col-6">
         <div class="todo branding">
            <img src="documents/img/sw_logo.png" width="200" alt="shopware AG">
         </div>
      </div>
   </div>
   <div class="clear"></div>

</div>

<div class="row">
   <div class="col-6">

      <div class="container-to">

         <h1>Rechnung</h1>

         <div class="spacer"></div>

         <div class="text-invoice-to">
            <strong>{$receiverAddress.company}</strong> <br>
            {$receiverAddress.firstName} {$receiverAddress.lastName} <br>
            {$receiverAddress.street} <br>
            {$receiverAddress.zipCode} {$receiverAddress.city} <br>
            <span class="todo">NRW - Germany</span> <br>
         </div>

      </div>

   </div>
   <div class="col-6">

      <div class="todo box-colored">

         shopware AG <br>
         Eggeroder Str. 6 <br>
         48624 Schöppingen <br>

         Fon: 01234 / 56789 <br>
         Fax: 01234 / 56780 <br>
         E-Mail: info@demo.de <br>
         Web: www.demo.de

      </div>

      <div class="text-docinfo">
         Rechnungsnummer: {$documentNumber} <br>
         Bestellnummer: {$orderNumber} <br>
         Seite <span class="pagenum"></span>
      </div>


   </div>
</div>
<div style="clear: both;"></div>

<table class="todo table-invoice" cellpadding="0" cellspacing="0" style="width: 100%; margin: 50px 0 0 0px;">
   <tr>
      <td>Pos.</td>
      <td>Art-Nr.</td>
      <td>Bezeichnung</td>
      <td class="text-center">Anz.</td>
      <td class="text-center">MwSt.</td>
      <td class="text-right">Einzelpreis</td>
      <td class="text-right">Gesamt</td>
   </tr>
   {foreach from=$items item=item key=i}
   <tr>
      <td>{$i+1}</td>
      <td>{$item.articleNumber}</td>
      <td class="text-color">{$item.articleName}</td>
      <td class="text-center">{$item.quantity}</td>
      <td class="text-center">{$item.tax}%</td>
      <td class="text-right">{$item.price|currency}</td>
      <td class="text-right">{$item.amount|currency}</td>
   </tr>
   {/foreach}
   <tr class="footer">
      <td colspan="4"></td>
      <td colspan="3">

         <table cellpadding="0" cellspacing="0" width="100%">
            <tr>
               <td class="text-right">Gesamtkosten Netto:</td>
               <td class="text-right">{$orderAmountNet|currency}</td>
            </tr>
            {foreach from=$tax item=taxAmount key=taxValue}
            <tr>
               <td class="text-right">zzgl. {$taxValue}% MwSt:</td>
               <td class="text-right">{$taxAmount|currency}</td>
            </tr>
            {/foreach}
            <tr>
               <td style="text-align: right; font-weight: bold;" class="text-color">Gesamtkosten:</td>
               <td style="text-align: right; font-weight: bold;" class="text-color">{$orderAmount|currency}</td>
            </tr>
         </table>

      </td>
   </tr>
</table>

<div class="text-meta">

   <div class="col-6">
      <strong>Gewählte Versandart:</strong> <span class="todo">Standard Versand</span> <br>
   </div>
   <div class="col-6 text-right">
      <strong>Gewählte Zahlungsart:</strong> <span class="todo">Rechnung</span> <br>
   </div>

   <div class="clear"></div>

   <strong>Kommentar:</strong> <span class="todo">Hier steht der Kunden Kommentar...</span>

   <br>

   <strong>Information:</strong> <span class="todo">Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.</span> <br>

</div>

<div class="text-thanks">
   <span class="todo">Vielen Dank für Ihre Bestellung</span>
</div>

</body>
</html>
