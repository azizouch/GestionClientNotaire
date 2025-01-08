<?php

declare(strict_types=1);

namespace migration_back_up;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020185434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compromis ADD designation_id INT NOT NULL');
        $this->addSql('ALTER TABLE compromis ADD CONSTRAINT FK_B604C7DDFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B604C7DDFAC7D83F ON compromis (designation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compromis DROP FOREIGN KEY FK_B604C7DDFAC7D83F');
        $this->addSql('DROP INDEX UNIQ_B604C7DDFAC7D83F ON compromis');
        $this->addSql('ALTER TABLE compromis DROP designation_id');
    }
}
