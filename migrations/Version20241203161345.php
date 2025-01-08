<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203161345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE procuration_personne_physique (procuration_id INT NOT NULL, personne_physique_id INT NOT NULL, INDEX IDX_321444ECEBFD383 (procuration_id), INDEX IDX_321444E54472AC9 (personne_physique_id), PRIMARY KEY(procuration_id, personne_physique_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE procuration_personne_physique ADD CONSTRAINT FK_321444ECEBFD383 FOREIGN KEY (procuration_id) REFERENCES procuration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_personne_physique ADD CONSTRAINT FK_321444E54472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE procuration_personne_physique DROP FOREIGN KEY FK_321444ECEBFD383');
        $this->addSql('ALTER TABLE procuration_personne_physique DROP FOREIGN KEY FK_321444E54472AC9');
        $this->addSql('DROP TABLE procuration_personne_physique');
    }
}
