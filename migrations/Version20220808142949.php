<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220808142949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE order_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE order_item (id INT NOT NULL, order_id INT NOT NULL, product_name VARCHAR(255) NOT NULL, product_id INT NOT NULL, product_price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52EA1F09FCDAEAAA ON order_item (order_id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09FCDAEAAA FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('DROP INDEX category_name_key');
//        $this->addSql('CREATE SEQUENCE category_id_seq');
//        $this->addSql('SELECT setval(\'category_id_seq\', (SELECT MAX(id) FROM category))');
//        $this->addSql('ALTER TABLE category ALTER id SET DEFAULT nextval(\'category_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('CREATE SCHEMA public');
//        $this->addSql('DROP SEQUENCE order_item_id_seq CASCADE');
//        $this->addSql('DROP TABLE order_item');
//        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
//        $this->addSql('CREATE UNIQUE INDEX category_name_key ON category (name)');
    }
}
