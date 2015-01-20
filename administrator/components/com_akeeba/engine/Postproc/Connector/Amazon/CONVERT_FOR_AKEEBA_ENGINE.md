# Notice 

The AWS SDK for PHP has been reduced to a subset and a different namespace was applied to it for use with Akeeba Engine.
The license of the original code can be found in the LICENSE.md document in the same directory. For the full, unmodified
AWS SDK for PHP please see the README.md file.

# Steps performed to create this Derivative Work

1. Removed all `Aws` subdirectories except `Common` and `S3`

2. Removed the `aws-autoloader.php` file

3. Deleted the top level `Doctrine`, `Monolog` and `Psr` directories

3. Performed the following replacements throughout the code. Each replacement pair is given in "FROM newline TO" format.

	namespace Aws\
	namespace Akeeba\Engine\Postproc\Connector\Amazon\Aws\

	namespace Guzzle\
	namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\

	namespace Symfony\
	namespace Akeeba\Engine\Postproc\Connector\Amazon\Symfony\

	use Aws\
	use Akeeba\Engine\Postproc\Connector\Amazon\Aws\

	use Guzzle\
	use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\
	
	Use Guzzle\
	use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\	

	use Symfony\
	use Akeeba\Engine\Postproc\Connector\Amazon\Symfony\

	'Aws\\
	'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Aws\\

	'Guzzle\\
	'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\
	
	'Guzzle\Service\Command\LocationVisitor\Request\
	'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Service\\Command\\LocationVisitor\\Request\\
	
	'Guzzle\Service\Command\LocationVisitor\Response\
	 'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Service\\Command\\LocationVisitor\\Response\\
	
	'Guzzle\Service\Command\Factory\ServiceDescriptionFactory'
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Service\\Command\\Factory\\ServiceDescriptionFactory'
    
    'Guzzle\Http\EntityBody'
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Http\\EntityBody'
    
    'Guzzle\Batch\
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Batch\\
    
    'Guzzle\Cache\NullCacheAdapter'
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Cache\\NullCacheAdapter'
    
    'Guzzle\Http\Message\
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Http\\Message\\
    
    'Guzzle\Http\Exception\
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Http\\Exception\\
    
    'Guzzle\Plugin\Cache\DefaultCanCacheStrategy'
    'Akeeba\\Engine\\Postproc\\Connector\\Amazon\\Guzzle\\Plugin\\Cache\\DefaultCanCacheStrategy'
    
    new \Guzzle\Plugin\Cache\DefaultCanCacheStrategy();
    new \Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Plugin\Cache\DefaultCanCacheStrategy();

4. Copied the resulting subset into <engineroot>/Postproc/Connector/Amazon

5. This file was added to warn the users that this is a Derivative Work, per the original work's license agreement.

## Bug fixes applied

### v4 signatures and MD5 content sums don't mix

Changed the Aws\Common\Signature\SignatureV4::createSigningContext method to consider the `content-md5` header when calculating the request signature. Similar to fix https://github.com/aws/aws-sdk-php/commit/0b856068520707bbf92930a5f3cfe1d5512f8f2a without the method restructuring.