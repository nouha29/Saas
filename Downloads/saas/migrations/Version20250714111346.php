<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714111346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE composition (id INT AUTO_INCREMENT NOT NULL, consommation DOUBLE PRECISION NOT NULL, nomenclature_id INT NOT NULL, matiere_id INT NOT NULL, INDEX IDX_C7F434790BFD4B8 (nomenclature_id), INDEX IDX_C7F4347F46CD258 (matiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE composition ADD CONSTRAINT FK_C7F434790BFD4B8 FOREIGN KEY (nomenclature_id) REFERENCES nomenclature (id)');
        $this->addSql('ALTER TABLE composition ADD CONSTRAINT FK_C7F4347F46CD258 FOREIGN KEY (matiere_id) REFERENCES articles (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE composition DROP FOREIGN KEY FK_C7F434790BFD4B8');
        $this->addSql('ALTER TABLE composition DROP FOREIGN KEY FK_C7F4347F46CD258');
        $this->addSql('DROP TABLE composition');
    }
}
