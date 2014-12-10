<?php
	if (!function_exists('ratingwidget'))
	{
		class RatingWidgetPlugin {
			function RW_IsTrial() {
				return false;
			}

			function RW_IsPaying() {
				return false;
			}

			function UpdateSecret( $new_secret ) {
				return;
			}

			function GetUpgradeUrl( $pImmediate = false, $pPeriod = 'annually', $pPlan = 'professional' ) {
				return 'https://freemius.com/dummy/upgrade/url/';
			}
		}

		function ratingwidget() {
			global $rwp;

			if (!isset($rwp)) {
				$rwp = new RatingWidgetPlugin();
			}

			return $rwp;
		}
	}