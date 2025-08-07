# 🚀 Docker Easy Deployment Guide

---

## 🔁 Direct Clone from GitHub

### ✅ Step 1: Clone the repository

```bash
git clone https://github.com/zamibd/docker-easy.git
```

---

### ✅ Step 2: Enter the project directory

```bash
cd docker-easy
```

---

### ✅ Step 3: Edit your domain name (NGINX config)

```bash
nano docker/nginx/default.conf
```

---

### ✅ Step 4: Rename and edit environment file

```bash
cp .env.example .env
nano .env
```

---

### ✅ Step 5: Edit Redis config (if needed)

```bash
nano docker/redis/redis.conf
```

---

### ✅ Step 6: Install Docker, Docker Compose, Git, and run your project

```bash
bash install.sh
```

---
### run install.sh from curl

```bash
bash <(curl -sSL https://raw.githubusercontent.com/zamibd/docker-easy/refs/heads/main/install.sh)
```
---

## 💻 Setup from Local Computer using Terraform

### ✅ Step 1: Clone the repository

```bash
git clone https://github.com/zamibd/docker-easy.git
```

---

### ✅ Step 2: Enter the Terraform directory

```bash
cd docker-easy/terraform
```

---

### ✅ Step 3: Edit variables (requires Linode API token and SSH password)

```bash
nano terraform.tfvars
```

---

### ✅ Step 4: Initialize Terraform

```bash
terraform init
```

---

### ✅ Step 5: Review the Terraform plan

```bash
terraform plan
```

---

### ✅ Step 6: Apply the plan to provision VPS and deploy Docker project

```bash
terraform apply
```

---

✅ **That's it! Your Docker-based project is now ready to run and scale!**


