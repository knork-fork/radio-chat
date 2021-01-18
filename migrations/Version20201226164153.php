<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201226164153 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE friend_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE friend (id INT NOT NULL, user_source_id INT NOT NULL, user_target_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_55EEAC6195DC9185 ON friend (user_source_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC61156E8682 ON friend (user_target_id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC6195DC9185 FOREIGN KEY (user_source_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61156E8682 FOREIGN KEY (user_target_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE friend_id_seq CASCADE');
        $this->addSql('DROP TABLE friend');
    }
}
