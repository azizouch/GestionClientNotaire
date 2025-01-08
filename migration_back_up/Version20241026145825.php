<?php

declare(strict_types=1);

namespace migration_back_up;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241026145825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE designation ADD nombre_salon INT NOT NULL, ADD nombre_chambre INT NOT NULL, ADD nombre_cuisine INT NOT NULL, ADD nombre_sallon_de_bain INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE designation DROP nombre_salon, DROP nombre_chambre, DROP nombre_cuisine, DROP nombre_sallon_de_bain');
    }
}
