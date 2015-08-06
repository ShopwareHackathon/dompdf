<?php

class Migration_506 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        $this->addSql('ALTER TABLE s_core_documents ADD COLUMN legacy int(1) NOT NULL DEFAULT 0;');

        if ($modus == Shopware\Components\Migrations\AbstractMigration::MODUS_UPDATE) {
            $this->addSql('UPDATE s_core_documents SET legacy = 1;');
        }
    }
}
