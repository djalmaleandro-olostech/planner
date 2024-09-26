locals {
  # Default
  environment        = var.environment
  base_name          = "php-service"
  project_name       = "${local.base_name}-${local.environment}"
  project_short_name = local.base_name
  region             = "us-east-1"
  application_port   = 9501

  # Network
  vpc_name = "php-hyperf"
  subnet_names = [
    "${local.vpc_name}-private-us-east-1a",
    "${local.vpc_name}-private-us-east-1b",
  ]

  public_subnet_names = [
    "${local.vpc_name}-public-us-east-1a",
    "${local.vpc_name}-public-us-east-1b",
  ]

  # LB
  lb_api = {
    lb_internal              = true
    lb_type                  = "application"
    lb_target_group_protocol = "HTTP"
    lb_target_type           = "ip"
    lb_listener_port         = "443"
    lb_listener_protocol     = "HTTPS"
    lb_target_group_port     = local.application_port

    lb_healthcheck = {
      enabled             = true
      interval            = 30
      path                = "/live"
      port                = local.application_port
      protocol            = "HTTP"
      timeout             = 20
      healthy_threshold   = 2
      unhealthy_threshold = 2
      matcher             = "200"
    }
  }


  # ECR
  ecr_repo_name = "ecr-${local.project_name}"

  # ECS
  ecs_cluster_name = "ecs-cluster-${local.project_name}"
  ecs_task = {
    family = "ecs-task-${local.project_name}"
    memory = 1024
    cpu    = 512
  }
  ecs_service_name = "${local.project_name}-web"
  ecs_service = {
    desired_count           = "1"
    maximum_percent         = "200"
    minimum_healthy_percent = "100"
  }

  # APP Auto Scaling
  appautoscaling = {
    max_capacity              = var.appautoscaling.max_capacity
    min_capacity              = var.appautoscaling.min_capacity
    policy_target_value       = var.appautoscaling.policy_target_value
    policy_scale_in_cooldown  = var.appautoscaling.policy_scale_in_cooldown
    policy_scale_out_cooldown = var.appautoscaling.policy_scale_out_cooldown
  }

  cron_project_name       = "${local.project_name}-cron"
  cron_project_short_name = "${local.project_name}-cron"

  cron_ecs_task = {
    family = "ecs-task-${local.cron_project_name}"
    memory = "2048"
    cpu    = "1024"
  }

  cron_ecs_service_name = local.cron_project_name
  cron_ecs_service = {
    desired_count           = "1"
    maximum_percent         = "100"
    minimum_healthy_percent = "0"
  }


  # Route53
  route53_record_name = "${local.environment}.domain.net"

  #Elasticache
  availability_zones = ["us-east-1a", "us-east-1b"]
  create_redis       = local.environment == "prod"
  create_redis_count = local.create_redis ? 1 : 0


  tags = {
    Environment = var.environment
    Name        = local.project_name
    Repository  = data.tfe_workspace.workspace.vcs_repo[0].identifier
  }
}
