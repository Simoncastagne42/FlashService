<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720195641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955D62B0FA');
    $this->addSql('DROP INDEX UNIQ_42C84955D62B0FA ON reservation');
    $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955D62B0FA FOREIGN KEY (time_slot_id) REFERENCES time_slot (id)');
}

      public function down(Schema $schema): void
    {
        // Supprimer la contrainte étrangère sans contrainte unique
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955D62B0FA');

        // Recréer l'index unique sur time_slot_id
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42C84955D62B0FA ON reservation (time_slot_id)');

        // Recréer la contrainte étrangère avec contrainte unique (1-1)
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955D62B0FA FOREIGN KEY (time_slot_id) REFERENCES time_slot (id)');
    }
}
