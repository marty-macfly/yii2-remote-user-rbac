<?php

namespace macfly\user;

use Yii;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
	public $authclient				= null;
	public $identityUrl				= null;
	public $rbacUrl						= null;

	public $clientCollection	= 'authClientCollection';
	public $remoteModelMap		= [
			'app\models\User'	=> 'User',
		];

	/** @var array Model map */
	public $modelMap		= [];

	/** @var int The time you want the user will be remembered without asking for credentials. */
	public $rememberFor	= 1209600; // two week

	public function identity($method, $args)
	{
		if(is_null($this->identityUrl))
		{
       throw new NotSupportedException("Property 'identityUrl' not defined");
		}

		return $this->request($method, $this->identityUrl, $args);
	}

	public function rbac($method, $args)
	{
		if(is_null($this->identityUrl))
		{
       throw new NotSupportedException("Property 'rbacUrl' not defined");
		}

		return $this->request($method, $this->rbacUrl, $args);
	}

	protected function request($method, $url, $args = [])
  {
#		if(($arr = Yii::$app->cache->get($id)) === false)
#		{
			$client = Yii::$app->get($this->clientCollection)->getClient($this->authclient);
			$rq     = $client->createApiRequest()
								->setMethod('PUT')
								->setUrl(sprintf("%s/%s", $url, $method))
								->setData($args);
			$rs			= $rq->send();

			if(!$rs->isOk)
			{
				 throw new NotSupportedException(sprintf("Error requesting method '%s' : %s", $method, $rs->content));
			}

			$arr = $rs->data;
#			Yii::$app->cache->set($arr['id'], $arr, $this->rememberFor);
#		}

    if(is_array($arr) && array_key_exists('class', $arr))
    {
			if(array_key_exists($arr['class'], $this->remoteModelMap))
			{
				$arr['class']	= $this->modelMap[$this->remoteModelMap[$arr['class']]];
			}
      return \Yii::createObject($arr);
    }

    return $arr;
  }
}