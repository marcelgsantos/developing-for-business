<?php

namespace Develop\Business\Wishlist\Tests\UseCases;

use Develop\Business\Wishlist\Intentions\NotifyProductAvailable as NotifyProductAvailableIntention;
use Develop\Business\Wishlist\Item;
use Develop\Business\Wishlist\ItemResolver;
use Develop\Business\Wishlist\NotifierInterface;
use Develop\Business\Wishlist\Status;
use Develop\Business\Wishlist\UseCases\NotifyProductAvailable;
use Develop\Business\Wishlist\UseCases\NotifyProductsAvailable;
use Develop\Business\Wishlist\Repositories\Wishlist as WishlistRepository;
use Develop\Business\Wishlist\Factory as WishlistFactory;
use Develop\Business\Wishlist\Wishlist;
use Prophecy\Argument;

class NotifyProductAvailableTest extends \PHPUnit_Framework_TestCase
{
    public function testNotifyCustomerWhenProductIsAvailable()
    {
        $item = new Item(1, 'Shoes', false);

        $resolver = $this->prophesize(ItemResolver::class);
        $resolver->resolve(1)->willReturn($item);

        $notifier = $this->prophesize(NotifierInterface::class);
        $notifier->send(Argument::type(Wishlist::class))->shouldBeCalled();

        $intention = new NotifyProductAvailableIntention($notifier->reveal(), 1);
        $factory = new WishlistFactory();
        $wishlistPending = $factory->fromQueryResult(1, 'email@test.com', 1, 'T-Shirt', false, Status::PENDING);
        $wishlistSent = $factory->fromQueryResult(1, 'email@test.com', 1, 'T-Shirt', false, Status::SENT);

        $repository = $this->prophesize(WishlistRepository::class);
        $repository->findAllCustomersByItem($item)
            ->willReturn([$wishlistPending]);
        $repository->update($wishlistSent)->willReturn(true);

        $useCase = new NotifyProductAvailable($repository->reveal(), $resolver->reveal());
        $this->assertTrue($useCase->execute($intention));
    }
}
