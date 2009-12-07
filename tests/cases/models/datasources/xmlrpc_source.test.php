<?php

App::import('Datasource', 'XmlrpcDatasource.XmlrpcSource');

class XmlrpcSourceTest extends CakeTestCase {

	var $Xmlrpc = null;

	function setUp() {
		parent::setUp();
		$this->Xmlrpc =& new XmlrpcSource();
	}

	function testGenerateXMLWithoutParams() {
		$header = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>' . "\n";
		$expected = $header . '<methodCall><methodName>test</methodName><params /></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test'));
	}

	function testGenerateXMLOneParam() {
		$header = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>' . "\n";

		// Integer
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><int>1</int></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(1)));

		// Double
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><double>5.2</double></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(5.2)));

		// String
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><string>testing</string></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array('testing')));

		// Boolean
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><boolean>0</boolean></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(false)));
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><boolean>1</boolean></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(true)));

		// Array
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><array><data><value><int>12</int></value><value><string>Egypt</string></value><value><boolean>0</boolean></value><value><int>-31</int></value></data></array></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(array(12, 'Egypt', false, -31))));

		// Struct
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><struct><member><name>lowerBound</name><value><int>18</int></value></member><member><name>upperBound</name><value><int>139</int></value></member></struct></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(array('lowerBound' => 18, 'upperBound' => 139))));
	}

	function testGenerateXMLMultiParams() {
		$header = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>' . "\n";

		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><int>1</int></value></param><param><value><string>testing</string></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(1, 'testing')));

		$expected = $header . '<methodCall><methodName>test</methodName><params>';
		$expected .= '<param><value><array><data><value><int>12</int></value><value><string>Egypt</string></value><value><boolean>0</boolean></value><value><int>-31</int></value></data></array></value></param>';
		$expected .= '<param><value><int>1</int></value></param>';
		$expected .= '<param><value><struct><member><name>test</name><value><boolean>1</boolean></value></member></struct></value></param>';
		$expected .= '</params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(array(12, 'Egypt', false, -31), 1, array('test' => true))));
	}

	function testGenerateXMLMultiDimensions() {
		$header = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>' . "\n";

		// Array
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><array><data><value><int>1</int></value><value><array><data><value><int>2</int></value><value><string>b</string></value></data></array></value></data></array></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(array(1, array(2, 'b')))));

		// Struct
		$expected = $header . '<methodCall><methodName>test</methodName><params><param><value><struct><member><name>base</name><value><struct><member><name>value</name><value><double>-50.72</double></value></member></struct></value></member></struct></value></param></params></methodCall>';
		$this->assertEqual($expected, $this->Xmlrpc->generateXML('test', array(array('base' => array('value' => -50.720)))));
	}

	function testParseResponse() {
		// Integer
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><int>555</int></value></param></params></methodResponse>';
		$this->assertEqual(555, $this->Xmlrpc->parseResponse($xml));
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><i4>555</i4></value></param></params></methodResponse>';
		$this->assertEqual(555, $this->Xmlrpc->parseResponse($xml));

		// Double
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><double>57.20</double></value></param></params></methodResponse>';
		$this->assertEqual(57.2, $this->Xmlrpc->parseResponse($xml));

		// String
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><string>South Dakota</string></value></param></params></methodResponse>';
		$this->assertEqual('South Dakota', $this->Xmlrpc->parseResponse($xml));

		// Boolean
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><boolean>1</boolean></value></param></params></methodResponse>';
		$this->assertEqual(true, $this->Xmlrpc->parseResponse($xml));

		// Array
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><array><data><value><int>1</int></value><value><string>testing</string></value></data></array></value></param></params></methodResponse>';
		$this->assertEqual(array(1, 'testing'), $this->Xmlrpc->parseResponse($xml));
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><array><data><value><array><data><value><string>a</string></value><value><string>b</string></value></data></array></value><value><string>testing</string></value></data></array></value></param></params></methodResponse>';
		$this->assertEqual(array(array('a', 'b'), 'testing'), $this->Xmlrpc->parseResponse($xml));

		// Struct
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><struct><member><name>test</name><value><string>testing</string></value></member><member><name>boolean</name><value><boolean>1</boolean></value></member></struct></value></param></params></methodResponse>';
		$this->assertEqual(array('test' => 'testing', 'boolean' => true), $this->Xmlrpc->parseResponse($xml));
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><params><param><value><struct><member><name>test</name><value><struct><member><name>a</name><value><string>b</string></value></member><member><name>c</name><value><string>d</string></value></member></struct></value></member><member><name>test2</name><value><array><data><value><int>1</int></value><value><i4>2</i4></data></array></value></member></struct></value></param></params></methodResponse>';
		$this->assertEqual(array('test' => array('a' => 'b', 'c' => 'd'), 'test2' => array(1, 2)), $this->Xmlrpc->parseResponse($xml));
	}

	function testParseResponseError() {
		$xml = '<' . '?xml version="1.0"?' .'><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>4</int></value></member><member><name>faultString</name><value><string>Too many parameters.</string></value></member></struct></value></fault></methodResponse>';
		$this->assertFalse($this->Xmlrpc->parseResponse($xml));
		$this->assertEqual(4, $this->Xmlrpc->errno);
		$this->assertEqual('Too many parameters.', $this->Xmlrpc->error);
	}

}

?>