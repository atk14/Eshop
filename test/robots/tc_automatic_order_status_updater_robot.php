<?php
/**
 *
 * @fixture orders
 */
class TcAutomaticOrderStatusUpdaterRobot extends TcBase {

	function test(){
		$day = 60 * 60 * 25;

		$robot = new AutomaticOrderStatusUpdaterRobot();
		$robot->__runRobot();
		$this->assertStringContains("total changes: 0",$robot->logger->buffer->toString());

		// nothing should be changed
		$order = $this->orders["test"];
		$order->s("order_status_set_at",date("Y-m-d H:i:s",time() - 20 * $day));
		$ret = $order->setNewOrderStatus([
			"order_status_id" => OrderStatus::GetInstanceByCode("repeated_payment_request"),
			"order_status_set_at" => date("Y-m-d H:i:s",time() - 5 * $day),
		]);
		$order->setNewOrderStatus("waiting_for_bank_transfer");
		$robot = new AutomaticOrderStatusUpdaterRobot();
		$robot->__runRobot();
		$this->assertStringContains("total changes: 0",$robot->logger->buffer->toString());
		$order = Order::GetInstanceById($order); // re-read order
		$this->assertEquals("waiting_for_bank_transfer",$order->getOrderStatus()->getCode());

		// order status should be updated
		$order = $this->orders["test_bank_transfer"];
		$order->s("order_status_set_at",date("Y-m-d H:i:s",time() - 20 * $day));
		$ret = $order->setNewOrderStatus([
			"order_status_id" => OrderStatus::GetInstanceByCode("repeated_payment_request"),
			"order_status_set_at" => date("Y-m-d H:i:s",time() - 15 * $day),
		]);
		$robot = new AutomaticOrderStatusUpdaterRobot();
		$robot->__runRobot();
		$this->assertStringContains("total changes: 1",$robot->logger->buffer->toString());
		$order = Order::GetInstanceById($order); // re-read order
		$this->assertEquals("payment_failed",$order->getOrderStatus()->getCode());
		$this->assertTrue($order->getOrderStatusSetByUser()->isRobot());
	}
}
