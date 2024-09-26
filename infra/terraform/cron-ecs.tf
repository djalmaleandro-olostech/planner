resource "aws_ecs_task_definition" "php-service-cron" {
  family                   = local.cron_ecs_task.family
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
      environment  = []
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
      environment = concat([
        {
          name  = "ACTIVE_CRON"
          value = "true"
        }
      ], local.ecs_environments)
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
  memory             = local.cron_ecs_task.memory
  cpu                = local.cron_ecs_task.cpu
  execution_role_arn = aws_iam_role.ecs-task-role.arn
  task_role_arn      = aws_iam_role.ecs-task-role.arn

  tags     = local.tags
  tags_all = local.tags
}

resource "aws_security_group" "allow_from_vpc_cron" {
  name        = format("allow-%s", local.cron_project_short_name)
  description = "Allow from VPC inbound traffic"
  vpc_id      = data.aws_vpc.default-vpc.id

  ingress {
    description = "TLS from VPC"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = [data.aws_vpc.default-vpc.cidr_block]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags     = merge({ Name = format("sg-%s", local.cron_project_name) }, local.tags)
  tags_all = merge({ Name = format("sg-%s", local.cron_project_name) }, local.tags)
}

resource "aws_ecs_service" "php-service-cron" {
  name                               = local.cron_ecs_service_name
  cluster                            = aws_ecs_cluster.cluster-php-service.id
  desired_count                      = local.cron_ecs_service.desired_count
  launch_type                        = "FARGATE"
  scheduling_strategy                = "REPLICA"
  deployment_maximum_percent         = local.cron_ecs_service.maximum_percent
  deployment_minimum_healthy_percent = local.cron_ecs_service.minimum_healthy_percent
  platform_version                   = "LATEST"
  task_definition                    = aws_ecs_task_definition.php-service-cron.arn


  network_configuration {
    subnets          = data.aws_subnets.default-subnets.ids
    security_groups  = [aws_security_group.allow_from_vpc_cron.id]
    assign_public_ip = true
  }

  tags     = local.tags
  tags_all = local.tags

}