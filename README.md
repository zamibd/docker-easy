# 🚀 Docker Easy Deployment Guide

## 🔁 Direct Clone from GitHub

### 🧾 Step-by-step

```bash
# 1. Clone the repository
git clone https://github.com/zamibd/docker-easy.git

# 2. Enter the project directory
cd docker-easy

# 3. Edit your domain name (NGINX config)
nano docker/nginx/default.conf

# 4. Rename and edit environment file
cp .env.example .env
nano .env

# 5. Edit Redis config (if needed)
nano docker/redis/redis.conf

# 6. Install Docker, Docker Compose, Git, and run your project
bash install.sh

💻 Setup from Local Computer using Terraform

# 1. Clone the repository
git clone https://github.com/zamibd/docker-easy.git

# 2. Enter the Terraform directory
cd docker-easy/terraform

# 3. Edit variables (requires Linode API token and SSH password)
nano terraform.tfvars

# 4. Initialize Terraform
terraform init

# 5. Review plan
terraform plan

# 6. Apply the plan (provision VPS and deploy Docker project)
terraform apply
