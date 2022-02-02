//package main
//
//import (
//	"github.com/riotkit-org/backup-repository/http"
//)
//
//func main() {
//	// todo: First thread - HTTP
//	// todo: Second thread - configuration changes watcher
//	//       Notice: Fork configuration objects on each request? Or do not allow updating, when any request is pending?
//	http.SpawnHttpApplication()
//}

package main

import (
	"fmt"
	"github.com/riotkit-org/backup-repository/config"
	"log"
)

func main() {
	provider, err := config.CreateConfigurationProvider("kubernetes")
	if err != nil {
		log.Fatal(err)
	}

	json, err := provider.GetSingleDocument("GrantedAccess", "39671096ba800ca9b238c7c01b053fa0d5d09ca3151e050d148ddfffaefa9466ceba75d47c2098a0f72110aea4deeb24f6cd1b31f27e27aa6fe7b82dad956049")

	fmt.Println("!!!", json)

	//config, err := clientcmd.BuildConfigFromFlags("", "/home/krzysiek/.kube/config")
	//if err != nil {
	//	fmt.Println(err)
	//}
	////config.Insecure = true
	//
	//clientset, err := kubernetes.NewForConfig(config)
	//if err != nil {
	//	fmt.Println(err)
	//}
	//
	//informerFactory := informers.NewSharedInformerFactory(clientset, time.Second*30)
	//
	//podInformer := informerFactory.Core().V1().Pods()
	//podInformer.Informer().AddEventHandler(
	//	cache.ResourceEventHandlerFuncs{
	//		AddFunc: func(obj interface{}) {
	//			fmt.Printf("service added: %s \n", obj)
	//		},
	//		DeleteFunc: func(obj interface{}) {
	//			fmt.Printf("service deleted: %s \n", obj)
	//		},
	//		UpdateFunc: func(oldObj, newObj interface{}) {
	//			fmt.Printf("service changed \n")
	//		},
	//	},
	//)
	//
	//// add event handling for serviceInformer
	//
	//informerFactory.Start(wait.NeverStop)
	//informerFactory.WaitForCacheSync(wait.NeverStop)

	//watchlist := cache.NewListWatchFromClient(
	//	clientset.CoreV1().RESTClient(),
	//	"",
	//	v1.NamespaceAll,
	//	fields.Everything(),
	//)
	//_, controller := cache.NewInformer( // also take a look at NewSharedIndexInformer
	//	watchlist,
	//	nil,
	//	0, //Duration is int64
	//	cache.ResourceEventHandlerFuncs{
	//		AddFunc: func(obj interface{}) {
	//			fmt.Printf("service added: %s \n", obj)
	//		},
	//		DeleteFunc: func(obj interface{}) {
	//			fmt.Printf("service deleted: %s \n", obj)
	//		},
	//		UpdateFunc: func(oldObj, newObj interface{}) {
	//			fmt.Printf("service changed \n")
	//		},
	//	},
	//)
	//// I found it in k8s scheduler module. Maybe it's help if you interested in.
	//// serviceInformer := cache.NewSharedIndexInformer(watchlist, &v1.Service{}, 0, cache.Indexers{
	////     cache.NamespaceIndex: cache.MetaNamespaceIndexFunc,
	//// })
	//// go serviceInformer.Run(stop)
	//stop := make(chan struct{})
	//defer close(stop)
	//go controller.Run(stop)
	//for {
	//	time.Sleep(time.Second)
	//}
}
