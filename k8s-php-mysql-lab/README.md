# Kubernetes Lab 1: PHP + Apache + MySQL + phpMyAdmin

A containerized PHP student portal (login + registration) backed by MySQL,
deployed and managed entirely on Kubernetes, with phpMyAdmin for database
visibility.

## Architecture Diagram

```
        Browser
           |
           v
     App Service (NodePort 30080)
           |
           v
     PHP App Pods (Deployment, 3 replicas)
           |
           v
    MySQL Service (ClusterIP)
           |
           v
     MySQL Pod (Deployment)
           |
           v
   Persistent Volume Claim (mysql-pvc)

     phpMyAdmin Service (NodePort 30081)
           |
           v
     phpMyAdmin Pod  ---->  MySQL Service
```

## Repository Structure

```
k8s-php-mysql-lab/
├── app/
│   ├── config.php
│   ├── index.php
│   ├── register.php
│   ├── dashboard.php
│   └── logout.php
├── Dockerfile
├── database/
│   └── init.sql
├── kubernetes/
│   ├── mysql-deployment.yaml
│   ├── mysql-service.yaml
│   ├── mysql-secret.yaml
│   ├── phpmyadmin-deployment.yaml
│   ├── phpmyadmin-service.yaml
│   ├── app-deployment.yaml
│   ├── app-service.yaml
│   ├── configmap.yaml
│   └── pvc.yaml
└── README.md
```

## Setup Instructions

### Part 1 & 2: Build and containerize the app

```bash
docker build -t student-app .
docker run -p 8080:80 student-app   # verify it runs locally
```

> If using Minikube: run `eval $(minikube docker-env)` first so the image
> is built directly inside the cluster's Docker daemon (matches
> `imagePullPolicy: IfNotPresent` in `app-deployment.yaml`).

### Part 3: Deploy the database

```bash
kubectl apply -f kubernetes/mysql-secret.yaml
kubectl apply -f kubernetes/pvc.yaml
kubectl apply -f kubernetes/mysql-deployment.yaml
kubectl apply -f kubernetes/mysql-service.yaml
```

Verify:

```bash
kubectl get pods
kubectl get svc
kubectl get pvc
```

Load the schema once the MySQL pod is running:

```bash
kubectl exec -i deploy/mysql-deployment -- \
  mysql -uroot -p$(kubectl get secret mysql-secret -o jsonpath='{.data.MYSQL_ROOT_PASSWORD}' | base64 -d) \
  < database/init.sql
```

### Part 4: Deploy phpMyAdmin

```bash
kubectl apply -f kubernetes/phpmyadmin-deployment.yaml
kubectl apply -f kubernetes/phpmyadmin-service.yaml
```

Visit `http://<node-ip>:30081`, log in with the MySQL root credentials, and
confirm the `studentdb` database and `students` table are visible.

### Part 5: Deploy the application

```bash
kubectl apply -f kubernetes/configmap.yaml
kubectl apply -f kubernetes/app-deployment.yaml
kubectl apply -f kubernetes/app-service.yaml
```

Visit `http://<node-ip>:30080` — register a student, then log in.

### Part 6: Scaling

```bash
kubectl scale deployment app-deployment --replicas=3
kubectl get pods -l app=student-app
```

Refresh the dashboard page a few times — the "Served by pod" footer shows
requests being load-balanced across different pods.

### Part 7: Self-healing

```bash
kubectl get pods -l app=student-app
kubectl delete pod <pod-name>
kubectl get pods -l app=student-app -w
```

Kubernetes will automatically recreate the deleted pod to maintain the
desired replica count.

## Screenshots

1. Application homepage


3. Registration page
4. phpMyAdmin dashboard
5. Pods running (`kubectl get pods`)
6. Services running (`kubectl get svc`)
7. PVC created (`kubectl get pvc`)
8. Scaling to 3 replicas
9. Pod recreation after deletion

## Troubleshooting Notes

- **App pod can't connect to MySQL**: check `DB_HOST` in the ConfigMap
  matches the MySQL service name, and confirm the MySQL pod is `Running`
  (`kubectl logs deploy/mysql-deployment`).
- **phpMyAdmin shows "mysqli::real_connect(): php_network..."**: MySQL pod
  probably isn't ready yet — wait and check `kubectl get pods`.
- **Data lost after pod restart**: confirm the PVC is bound
  (`kubectl get pvc`) and mounted at `/var/lib/mysql` in the deployment spec.
