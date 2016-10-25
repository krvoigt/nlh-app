<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161025101318 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, startIpAddress, endIpAddress, institution, product FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER NOT NULL, startIpAddress INTEGER NOT NULL, endIpAddress INTEGER NOT NULL, institution VARCHAR(255) DEFAULT NULL COLLATE BINARY, product VARCHAR(30) NOT NULL COLLATE BINARY, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO user (id, startIpAddress, endIpAddress, institution, product) SELECT id, startIpAddress, endIpAddress, institution, product FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, startIpAddress, endIpAddress, institution, product FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER NOT NULL, startIpAddress VARCHAR(255) NOT NULL COLLATE BINARY, endIpAddress VARCHAR(255) NOT NULL COLLATE BINARY, institution VARCHAR(255) DEFAULT NULL, product VARCHAR(30) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO user (id, startIpAddress, endIpAddress, institution, product) SELECT id, startIpAddress, endIpAddress, institution, product FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
