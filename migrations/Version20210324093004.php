<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210324093004 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game CHANGE winner_id winner_id INT DEFAULT NULL, CHANGE date_ended date_ended DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE date_ended date_ended DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL, CHANGE derniere_connexion derniere_connexion DATE DEFAULT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game CHANGE winner_id winner_id INT DEFAULT NULL, CHANGE date_ended date_ended DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE round CHANGE date_ended date_ended DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user DROP is_verified, CHANGE avatar avatar VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE derniere_connexion derniere_connexion DATE DEFAULT \'NULL\', CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\'');
    }
}
