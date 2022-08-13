<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220808073658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE billing_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE billing_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
//        $this->addSql('INSERT INTO billing_type (name) VALUES ("Credit card"), ("Paypal"), ("Qiwi")');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE billing_type_id_seq CASCADE');
        $this->addSql('DROP TABLE billing_type');
        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX category_name_key ON category (name)');
    }
}
