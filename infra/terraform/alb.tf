resource "aws_lb" "lb-feed" {
  name                             = "lb-ecs-${local.project_short_name}"
  internal                         = local.lb_api.lb_internal
  load_balancer_type               = local.lb_api.lb_type
  idle_timeout                     = 60
  enable_cross_zone_load_balancing = true
  enable_deletion_protection       = false
  ip_address_type                  = "ipv4"
  security_groups                  = [aws_security_group.allow_web.id]
  subnets                          = data.aws_subnets.public-subnets.ids

  tags     = local.tags
  tags_all = local.tags
}

resource "aws_lb_target_group" "tg-ecs-feed" {
  name        = "tg-ecs-${local.project_short_name}"
  port        = local.lb_api.lb_target_group_port
  target_type = local.lb_api.lb_target_type
  protocol    = local.lb_api.lb_target_group_protocol
  vpc_id      = data.aws_vpc.default-vpc.id

  health_check {
    enabled             = local.lb_api.lb_healthcheck.enabled
    healthy_threshold   = local.lb_api.lb_healthcheck.healthy_threshold
    interval            = local.lb_api.lb_healthcheck.interval
    matcher             = local.lb_api.lb_healthcheck.matcher
    path                = local.lb_api.lb_healthcheck.path
    port                = local.lb_api.lb_healthcheck.port
    protocol            = local.lb_api.lb_healthcheck.protocol
    timeout             = local.lb_api.lb_healthcheck.timeout
    unhealthy_threshold = local.lb_api.lb_healthcheck.unhealthy_threshold
  }

  tags     = local.tags
  tags_all = local.tags

  lifecycle {
    create_before_destroy = true
  }
}

data "aws_acm_certificate" "php-hyperf-certifcate" {
  domain      = "*.${local.environment}.domain.net"
  most_recent = true
}

resource "aws_lb_listener" "lb-listener-feed" {
  load_balancer_arn = aws_lb.lb-feed.arn
  port              = local.lb_api.lb_listener_port
  protocol          = local.lb_api.lb_listener_protocol
  ssl_policy        = "ELBSecurityPolicy-2016-08"
  certificate_arn   = data.aws_acm_certificate.domain-certifcate.arn

  default_action {
    target_group_arn = aws_lb_target_group.tg-ecs-feed.arn
    type             = "forward"
  }

  tags     = local.tags
  tags_all = local.tags
}

