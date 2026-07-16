export default function ({
	second = 0,
	minute = 0,
	hour = 0,
	day = 0,
	week = 0,
	mounth = 0,
	year = 0,
}: {
	second?: number
	minute?: number
	hour?: number
	day?: number
	week?: number
	mounth?: number
	year?: number
}): number {
	mounth += year * 12
	day += week * 7
	day += mounth * 30
	hour += day * 24
	minute += hour * 60
	second += minute * 60

	return second * 1000
}
