<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814185650 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE league (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3EB4C31836AC99F1 (link), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(30) NOT NULL, last_name VARCHAR(40) NOT NULL, link VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_98197A6536AC99F1 (link), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_team (player_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_66FAF62C99E6F5DF (player_id), INDEX IDX_66FAF62C296CD8AE (team_id), PRIMARY KEY(player_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, league_id INT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C4E0A61F36AC99F1 (link), INDEX IDX_C4E0A61F58AFC4DE (league_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_team ADD CONSTRAINT FK_66FAF62C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_team ADD CONSTRAINT FK_66FAF62C296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F58AFC4DE FOREIGN KEY (league_id) REFERENCES league (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F58AFC4DE');
        $this->addSql('ALTER TABLE player_team DROP FOREIGN KEY FK_66FAF62C99E6F5DF');
        $this->addSql('ALTER TABLE player_team DROP FOREIGN KEY FK_66FAF62C296CD8AE');
        $this->addSql('DROP TABLE league');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE player_team');
        $this->addSql('DROP TABLE team');
    }
}
