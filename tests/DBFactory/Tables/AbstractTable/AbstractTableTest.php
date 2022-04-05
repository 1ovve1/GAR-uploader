<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use LAB2\DBFactory\Tables\AbstractTable\AbstractTable;
use LAB2\DBFactory\DBFacade;

final class AbstractTableTest extends TestCase
{
	use AbstractTable;

	const currTable = 'tests';

	private ?PDO 	$PDO = null;
	private ?string $name = self::currTable;
	private ?array 	$fields = null;
	private ?array  $metaInfo = null;
	private ?\PDOStatement $PDOInsert = null;

	/**
	 *  simple compare
	 * @return void
	 */
	public function testGetTableName() : void 
	{
		$input = 'SomeName';
		$output = 'some_name';

		$this->assertEquals($output, $this->getTableName($input	));
	}

	public function testMetaInfo() {
		$this->PDO = DBFacade::getInstance();
		$clearQuery = $this->PDO->query('DESCRIBE ' . self::currTable)->
						fetchAll(\PDO::FETCH_COLUMN);

		$this->assertEquals(
			$clearQuery,
			$this->getMetaInfo(self::currTable)['fields'],
			'test with fileds ' . implode(',', $clearQuery)
		);
	}

	/**
	 *  select test (full, id, message, where)
	 * @return void
	 */
	public function testSelect() : void 
	{
		$this->PDO = DBFacade::getInstance();

		$query = 'SELECT * FROM ' . self::currTable;
		$this->assertEquals(
			$this->PDO->query($query)
					  ->fetchAll(\PDO::FETCH_ASSOC), 
			$this->select('*'), 
			$query
		);

		foreach ($this->getMetaInfo(self::currTable)['fields'] as $field) {
			$query = 'SELECT ' . $field. ' FROM ' . self::currTable;

			$this->assertEquals(
				$this->PDO->query($query)->fetchAll(\PDO::FETCH_ASSOC), 
				$this->select($field),
				$query
			);	

			foreach (['<', '<=', '=' , '>', '>='] as $sign) {
				$query = 'SELECT * FROM ' . self::currTable . ' WHERE ' . $field . $sign . '2';

				$this->assertEquals(
					$this->PDO->query($query)->fetchAll(\PDO::FETCH_ASSOC), 
					$this->select('*', $sign, [$field, 2]), 
					$query
				);
			}
		}
	}

	/**
	 * insert test (5 times with random values)	
	 * @return void
	 */
	public function testInsert() : void
	{
		$this->PDO = DBFacade::getInstance();
		$this->metaInfo = $this->getMetaInfo(self::currTable)['meta'];
		$this->prepareInsertPDOStatement();

		for ($iter = 5; $iter > 0; --$iter) {
			$rnd = rand();
			$select = 'SELECT message FROM ' . self::currTable . ' WHERE message=' . $rnd;

			$this->insert(['message' => $rnd]);

			$this->assertEquals(
				$this->PDO->query($select)->fetchAll(\PDO::FETCH_ASSOC)[0]['message'],
				$rnd,
				'test insert query with ' . $rnd
			);
		}
	} 

}