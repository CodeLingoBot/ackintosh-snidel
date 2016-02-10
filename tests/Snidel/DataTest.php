<?php
class Snidel_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @requires PHP 5.3
     */
    public function constructorSetsPid()
    {
        $data = new Snidel_Data(1234);
        $ref = new ReflectionProperty($data, 'pid');
        $ref->setAccessible(true);
        $this->assertSame($ref->getValue($data), 1234);
    }

    /**
     * @test
     */
    public function writeAndRead()
    {
        $data = new Snidel_Data(getmypid());
        $data->write('foo');
        $this->assertSame($data->readAndDelete(), 'foo');
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function readAndDeleteThrowsExceptionWhenFailed()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('read'))
            ->getMock();

        $shm->expects($this->once())
            ->method('read')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $ref->setValue($data, $shm);
        try {
            $data->write('foo');
            $data->readAndDelete();
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $data->delete();
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function writeThrowsRuntimeExceptionWhenFailedToOpenShm()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('open'))
            ->getMock();

        $shm->method('open')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $ref->setValue($data, $shm);
        try {
            $data->write('foo');
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $data->delete();
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function writeThrowsRuntimeExceptionWhenFailedToWriteData()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('write'))
            ->getMock();

        $shm->expects($this->once())
            ->method('write')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $ref->setValue($data, $shm);
        try {
            $data->write('foo');
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $data->delete();
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function readThrowsRuntimeException()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('read'))
            ->getMock();

        $shm->expects($this->once())
            ->method('read')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $data->write('foo');
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $ref->setValue($data, $shm);
        try {
            $data->read();
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $data->delete();
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function deleteThrowsExceptionWhenFailedToOpenShm()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('open'))
            ->getMock();

        $shm->expects($this->once())
            ->method('open')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $data->write('foo');
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $originalShm = $ref->getValue($data);

        $ref->setValue($data, $shm);
        try {
            $data->delete();
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $ref->setValue($data, $originalShm);
            $data->delete();
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function deleteThrowsExceptionWhenFailedToDeleteShm()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('delete'))
            ->getMock();

        $shm->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $data->write('foo');
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $originalShm = $ref->getValue($data);

        $ref->setValue($data, $shm);
        try {
            $data->delete();
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $ref->setValue($data, $originalShm);
            $data->delete();
            throw $e;
        }
    }

    /**
     * @test
     */
    public function deleteIfExistsReturnsNull()
    {
        $data = new Snidel_Data(getmypid());
        $this->assertNull($data->deleteIfExists());

        $data->write('foo');
        $this->assertNull($data->deleteIfExists());
    }

    /**
     * @test
     * @expectedException Snidel_Exception_SharedMemoryControlException
     * @requires PHP 5.3
     */
    public function deleteIfExistsThrowsExceptionWhenFailedToDeleteShm()
    {
        $shm = $this->getMockBuilder('Snidel_SharedMemory')
            ->setConstructorArgs(array(getmypid()))
            ->setMethods(array('delete'))
            ->getMock();

        $shm->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new Snidel_Exception_SharedMemoryControlException));

        $data = new Snidel_Data(getmypid());
        $data->write('foo');
        $ref = new ReflectionProperty($data, 'shm');
        $ref->setAccessible(true);
        $originalShm = $ref->getValue($data);

        $ref->setValue($data, $shm);
        try {
            $data->deleteIfExists();
        } catch (Snidel_Exception_SharedMemoryControlException $e) {
            $ref->setValue($data, $originalShm);
            $data->delete();
            throw $e;
        }
    }
}
