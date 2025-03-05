<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304155832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE professional (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, compagny_name VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, adress_compagny VARCHAR(255) DEFAULT NULL, city_compagny VARCHAR(255) DEFAULT NULL, zip_code_compagny VARCHAR(255) DEFAULT NULL, phone_compagny VARCHAR(255) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B3B573AAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional ADD CONSTRAINT FK_B3B573AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional DROP FOREIGN KEY FK_B3B573AAA76ED395');
        $this->addSql('DROP TABLE professional');
    }
}
