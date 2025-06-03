<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250603172239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jour de la colonne likes : initialise à 0 et rend NOT NULL';
    }

    public function up(Schema $schema): void
    {
        // Met tous les likes existants à 0
        $this->addSql('UPDATE post SET likes = 0 WHERE likes IS NULL');
        // Rend la colonne NOT NULL
        $this->addSql('ALTER TABLE post ALTER likes SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Permet à la colonne d'être nullable à nouveau
        $this->addSql('ALTER TABLE post ALTER likes DROP NOT NULL');
    }
}