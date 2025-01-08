<?php

declare(strict_types=1);

namespace migration_back_up;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020183655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compromis (id INT AUTO_INCREMENT NOT NULL, date_promettant DATETIME DEFAULT NULL, date_beneficiaire DATETIME DEFAULT NULL, date_maitre DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compromis_client (compromis_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_351718D960C6E675 (compromis_id), INDEX IDX_351718D919EB6921 (client_id), PRIMARY KEY(compromis_id, client_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compromis_client ADD CONSTRAINT FK_351718D960C6E675 FOREIGN KEY (compromis_id) REFERENCES compromis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compromis_client ADD CONSTRAINT FK_351718D919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD role VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compromis_client DROP FOREIGN KEY FK_351718D960C6E675');
        $this->addSql('ALTER TABLE compromis_client DROP FOREIGN KEY FK_351718D919EB6921');
        $this->addSql('DROP TABLE compromis');
        $this->addSql('DROP TABLE compromis_client');
        $this->addSql('ALTER TABLE client DROP role');
    }
}
