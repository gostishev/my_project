<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220810081622 extends AbstractMigration
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
//        $this->addSql('ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F09FCDAEAAA');
//        $this->addSql('DROP INDEX IDX_52EA1F09FCDAEAAA');
        $this->addSql('ALTER TABLE order_item ADD product_quantity INT NOT NULL');
//        $this->addSql('ALTER TABLE order_item RENAME COLUMN order_id TO order_id_id');
//        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX IDX_52EA1F09FCDAEAAA ON order_item (order_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('CREATE SCHEMA public');
//        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
//        $this->addSql('CREATE UNIQUE INDEX category_name_key ON category (name)');
//        $this->addSql('ALTER TABLE order_item DROP CONSTRAINT fk_52ea1f09fcdaeaaa');
//        $this->addSql('DROP INDEX idx_52ea1f09fcdaeaaa');
//        $this->addSql('ALTER TABLE order_item ADD order_id INT NOT NULL');
//        $this->addSql('ALTER TABLE order_item DROP order_id_id');
//        $this->addSql('ALTER TABLE order_item DROP product_quontity');
//        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT fk_52ea1f09fcdaeaaa FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
//        $this->addSql('CREATE INDEX idx_52ea1f09fcdaeaaa ON order_item (order_id)');
    }
}
