<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408132838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__ingredient AS SELECT id, nom, unite, proteines, lipides, glucides, calories FROM ingredient');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('CREATE TABLE ingredient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, unite VARCHAR(10) NOT NULL, proteines DOUBLE PRECISION NOT NULL, lipides DOUBLE PRECISION NOT NULL, glucides DOUBLE PRECISION NOT NULL, calories DOUBLE PRECISION NOT NULL)');
        $this->addSql('INSERT INTO ingredient (id, name, unite, proteines, lipides, glucides, calories) SELECT id, nom, unite, proteines, lipides, glucides, calories FROM __temp__ingredient');
        $this->addSql('DROP TABLE __temp__ingredient');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6BAF78705E237E06 ON ingredient (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__ingredient AS SELECT id, name, unite, proteines, lipides, glucides, calories FROM ingredient');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('CREATE TABLE ingredient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, unite VARCHAR(10) NOT NULL, proteines DOUBLE PRECISION NOT NULL, lipides DOUBLE PRECISION NOT NULL, glucides DOUBLE PRECISION NOT NULL, calories DOUBLE PRECISION NOT NULL)');
        $this->addSql('INSERT INTO ingredient (id, nom, unite, proteines, lipides, glucides, calories) SELECT id, name, unite, proteines, lipides, glucides, calories FROM __temp__ingredient');
        $this->addSql('DROP TABLE __temp__ingredient');
    }
}
