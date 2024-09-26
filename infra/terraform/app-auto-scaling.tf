resource "aws_appautoscaling_target" "app-as-feed" {
  max_capacity       = local.appautoscaling.max_capacity
  min_capacity       = local.appautoscaling.min_capacity
  resource_id        = "service/${local.ecs_cluster_name}/${local.ecs_service_name}"
  scalable_dimension = "ecs:service:DesiredCount"
  service_namespace  = "ecs"
  depends_on         = [aws_ecs_service.ecs-php-service]
}

resource "aws_appautoscaling_policy" "app-as-policy-feed" {
  name               = format("%s-scale-cpu", local.ecs_service_name)
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.app-as-feed.id
  scalable_dimension = aws_appautoscaling_target.app-as-feed.scalable_dimension
  service_namespace  = aws_appautoscaling_target.app-as-feed.service_namespace

  target_tracking_scaling_policy_configuration {
    scale_in_cooldown  = local.appautoscaling.policy_target_value
    scale_out_cooldown = local.appautoscaling.policy_scale_in_cooldown
    target_value       = local.appautoscaling.policy_scale_out_cooldown

    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageCPUUtilization"
    }
  }
  depends_on = [aws_appautoscaling_target.app-as-feed]
}

