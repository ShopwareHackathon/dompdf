<?php

class Migrations_Migration507 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        if ($modus == Shopware\Components\Migrations\AbstractMigration::MODUS_UPDATE) {
            return true;
        }

        $this->addSql('UPDATE s_core_documents_box SET value = "<table class=\"table-footer\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin: 20px 50px 0 0;\">
        <tr>
            <td>Demo Shop GmbH</td>
            <td>Bankverbindung</td>
            <td>AGB</td>
            <td>Geschäftsführung</td>
        </tr>
        <tr>
            <td valign=\"top\">
                Steuer-Nr: DE 900 400 200 <br>
                UST-ID: DE 100 400 200 <br>
                Finanzamt Musterstadt
            </td>
            <td valign=\"top\">
                Sparkasse Musterstadt <br>
                IBAN: D3004005001003442353 <br>
                BIC: WELADEXX
            </td>
            <td valign=\"top\">
                Gerichtsstand ist Musterstadt <br>
                Erfüllungsort Musterstadt <br>
                Siehe auch mustershop.de/agb
            </td>
            <td valign=\"top\">
                Frank Mustermann <br>
                Sabine Musterfrau <br>
                Franz Demoname
            </td>
        </tr>
    </table>" WHERE name = "Footer"; ');
    }
}
