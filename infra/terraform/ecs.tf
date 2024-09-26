locals {
  ecs_environments = local.create_redis ? [
    {
      name  = "REDIS_RW_HOST"
      value = aws_route53_record.cache-cluster[0].fqdn
      }, {
      name  = "REDIS_RR_HOST"
      value = aws_route53_record.cache-cluster-read[0].fqdn
    }
  ] : []
}

resource "aws_ecs_cluster" "cluster-php-service" {
  name = local.ecs_cluster_name
  setting {
    name  = "containerInsights"
    value = "enabled"
  }
  tags     = local.tags
  tags_all = local.tags
}

resource "aws_ecs_task_definition" "php-service" {
  family                   = local.ecs_task.family
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  container_definitions = jsonencode([
    {
      essential = true,
      image     = "533243300146.dkr.ecr.${local.region}.amazonaws.com/newrelic/logging-firelens-fluentbit"
      name      = "log_router",
      firelensConfiguration = {
        type = "fluentbit"
        options = {
          enable-ecs-log-metadata = "true"
        }
      }
      cpu          = 0
      mountPoints  = []
      portMappings = []
      user         = "0"
      volumesFrom  = []
      stopTimeout  = 65
      }, {
      dnsSearchDomains = null
      logConfiguration = {
        logDriver = "awsfirelens"
        options = {
          Name = "newrelic"
        }
        secretOptions = [{
          name      = "apiKey"
          valueFrom = data.aws_ssm_parameter.newrelic-license.arn
          }
        ]
      }
      portMappings = [
        {
          hostPort      = local.application_port
          protocol      = "tcp"
          containerPort = local.application_port
        }
      ]
      environment = local.ecs_environments
      secrets = [{
        name      = "AWS_DEFAULT_REGION"
        valueFrom = aws_ssm_parameter.region.arn
      }]
      image     = "${aws_ecr_repository.ecr-php-service.repository_url}:latest"
      essential = true
      links     = []
      name      = local.project_name
    }
  ])
  memory             = local.ecs_task.memory
  cpu                = local.ecs_task.cpu
  execution_role_arn = aws_iam_role.ecs-task-role.arn
  task_role_arn      = aws_iam_role.ecs-task-role.arn

  tags     = local.tags
  tags_all = local.tags
}


resource "aws_ecs_service" "ecs-php-service" {
  name                               = local.ecs_service_name
  cluster                            = aws_ecs_cluster.cluster-php-service.id
  desired_count                      = local.ecs_service.desired_count
  launch_type                        = "FARGATE"
  scheduling_strategy                = "REPLICA"
  deployment_maximum_percent         = local.ecs_service.maximum_percent
  deployment_minimum_healthy_percent = local.ecs_service.minimum_healthy_percent
  platform_version                   = "LATEST"
  task_definition                    = aws_ecs_task_definition.php-service.arn

  load_balancer {
    target_group_arn = aws_lb_target_group.tg-ecs-feed.arn
    container_name   = local.project_name
    container_port   = local.lb_api.lb_target_group_port
  }

  network_configuration {
    subnets          = data.aws_subnets.default-subnets.ids
    security_groups  = [data.aws_security_group.default-sg.id]
    assign_public_ip = false
  }

  tags     = local.tags
  tags_all = local.tags

  propagate_tags = "SERVICE"

  lifecycle {
    ignore_changes = [task_definition, desired_count]
  }
}
