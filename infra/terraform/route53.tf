resource "aws_route53_record" "this" {
  name    = "${local.project_short_name}.${local.route53_record_name}"
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = true
    name                   = aws_lb.lb-feed.dns_name
    zone_id                = aws_lb.lb-feed.zone_id
  }
}

resource "aws_route53_record" "cache-cluster" {
  count   = local.create_redis_count
  name    = "rd-cluster-rw-${local.project_short_name}.${local.route53_record_name}"
  type    = "CNAME"
  zone_id = data.aws_route53_zone.public.id
  records = [replace(aws_elasticache_replication_group.cluster[0].primary_endpoint_address, ":6379", "")]
  ttl     = 60
}

resource "aws_route53_record" "cache-cluster-read" {
  count   = local.create_redis_count
  name    = "rd-cluster-rr-${local.project_short_name}.${local.route53_record_name}"
  type    = "CNAME"
  zone_id = data.aws_route53_zone.public.id
  records = [replace(aws_elasticache_replication_group.cluster[0].reader_endpoint_address, ":6379", "")]
  ttl     = 60
}


output "app-endpoint" {
  value = aws_route53_record.this.fqdn
}

