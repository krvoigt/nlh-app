<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * UserRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    /*
     * Compares client IP against Database-IPs to provide access
     *
     * @param string $clientIp The client IP
     */
    public function compareIp($clientIp)
    {
        return $this->getEntityManager()
                    ->createQuery('SELECT u FROM AppBundle:User u WHERE (:clientIp BETWEEN u.startIpAddress AND u.endIpAddress)')
                    ->setParameter('clientIp', $clientIp)
                    ->getResult();
    }

    /*
     * Creates database table by the given entity
     *
     * @param string $entityName The entity name
     * @param string $suffix The table name suffix
     */
    public function createTableByEntity($entityName, $suffix = null)
    {
        $schemaTool = new SchemaTool($this->getEntityManager());
        $metadata = $this->getEntityManager()->getClassMetadata($entityName);

        if ($suffix !== null) {
            $tableName = $metadata->getTableName().$suffix;
        } else {
            $tableName = $metadata->getTableName();
        }

        $metadata->setPrimaryTable(['name' => $tableName]);
        $schemaTool->createSchema([$metadata]);
    }

    /*
     * Checks if a given table exists
     *
     * @param string The table name
     *
     * @return boolean
     */
    public function checkIfTableExists($tableName)
    {
        $schemaManager = $this->getEntityManager()->getConnection()->getSchemaManager();
        if ($schemaManager->tablesExist(array($tableName)) === true) {
            return true;
        }

        return false;
    }

    /*
     * Stores a IP data row in the database
     *
     * @param integer $startIpAddress The start of an IP range
     * @param integer $endIpAddress The end of an IP range
     * @param string $institution The institution name
     * @param string $product The product name
     */
    public function storeTempDataRow($userTempTable, $startIpAddress, $endIpAddress, $institution, $product, $identifier)
    {
        $sql = 'INSERT INTO '.$userTempTable.' (startIpAddress, endIpAddress, institution, product, identifier) VALUES (:startIpAddress, :endIpAddress, :institution, :product, :identifier)';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('startIpAddress', $startIpAddress);
        $stmt->bindValue('endIpAddress', $endIpAddress);
        $stmt->bindValue('institution', $institution);
        $stmt->bindValue('product', $product);
        $stmt->bindValue('identifier', $identifier);
        $stmt->execute();
    }

    /*
     * Drops the original user table
     *
     * @param $userTable The user table name
     */
    public function dropUserTable($userTable)
    {
        $this->getEntityManager()->getConnection()->getSchemaManager()->dropTable($userTable);
    }

    /*
     * Renames the user temp table to user table
     *
     * @param $userTable The user table name
     * @param $userTempTable The user temp name
     */
    public function renameUserTempTable($userTable, $userTempTable)
    {
        $this->getEntityManager()->getConnection()->getSchemaManager()->renameTable($userTempTable, $userTable);
    }
}
