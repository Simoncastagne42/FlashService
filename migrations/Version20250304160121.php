<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304160121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE profession (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional ADD profession_id INT NOT NULL');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AAFDEF8996 FOREIGN KEY (profession_id) REFERENCES profession (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3B573AAFDEF8996 ON professional (profession_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AAFDEF8996');
        $this->addSql('DROP TABLE profession');
        $this->addSql('DROP INDEX UNIQ_B3B573AAFDEF8996 ON professional');
        $this->addSql('ALTER TABLE professional DROP profession_id');
    }
}
