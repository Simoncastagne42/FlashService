<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423080408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       
        $this->addSql('ALTER TABLE time_slot ADD CONSTRAINT FK_1B3294AED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('CREATE INDEX IDX_1B3294AED5CA9E6 ON time_slot (service_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_slot DROP FOREIGN KEY FK_1B3294AED5CA9E6');
        $this->addSql('DROP INDEX IDX_1B3294AED5CA9E6 ON time_slot');
        $this->addSql('ALTER TABLE time_slot DROP service_id');
    }
}
