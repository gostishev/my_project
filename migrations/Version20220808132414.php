<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220808132414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "order_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "order" (id INT NOT NULL, billing_type_id INT NOT NULL, customer_email VARCHAR(255) NOT NULL, shipment_date DATE NOT NULL, order_total NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F5299398AE620744 ON "order" (billing_type_id)');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398AE620744 FOREIGN KEY (billing_type_id) REFERENCES billing_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('DROP INDEX category_name_key');
//        $this->addSql('CREATE SEQUENCE category_id_seq');
//        $this->addSql('SELECT setval(\'category_id_seq\', (SELECT MAX(id) FROM category))');
//        $this->addSql('ALTER TABLE category ALTER id SET DEFAULT nextval(\'category_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('CREATE SCHEMA public');
//        $this->addSql('DROP SEQUENCE "order_id_seq" CASCADE');
//        $this->addSql('DROP TABLE "order"');
//        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
//        $this->addSql('CREATE UNIQUE INDEX category_name_key ON category (name)');
    }
}
