<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220814132223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('DROP INDEX category_name_key');
//        $this->addSql('CREATE SEQUENCE category_id_seq');
//        $this->addSql('SELECT setval(\'category_id_seq\', (SELECT MAX(id) FROM category))');
//        $this->addSql('ALTER TABLE category ALTER id SET DEFAULT nextval(\'category_id_seq\')');
//        $this->addSql('ALTER TABLE "order" ALTER shipment_date TYPE DATE');
//        $this->addSql('ALTER TABLE "order" ALTER shipment_date DROP DEFAULT');
//        $this->addSql('COMMENT ON COLUMN "order".shipment_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE order_item ALTER order_id DROP NOT NULL');
//        $this->addSql('ALTER INDEX idx_52ea1f09fcdaeaaa RENAME TO IDX_52EA1F098D9F6D38');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('CREATE SCHEMA public');
//        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
//        $this->addSql('CREATE UNIQUE INDEX category_name_key ON category (name)');
//        $this->addSql('ALTER TABLE "order" ALTER shipment_date TYPE DATE');
//        $this->addSql('ALTER TABLE "order" ALTER shipment_date DROP DEFAULT');
//        $this->addSql('COMMENT ON COLUMN "order".shipment_date IS NULL');
//        $this->addSql('ALTER TABLE order_item ALTER order_id SET NOT NULL');
//        $this->addSql('ALTER INDEX idx_52ea1f098d9f6d38 RENAME TO idx_52ea1f09fcdaeaaa');
    }
}
