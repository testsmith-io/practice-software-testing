export default class DiscountUtil {

  private static coordinates: any = {
    "new york": {
      lat: 41,
      lng: 74,
      discount_percentage: 5
    },
    "mumbai": {
      lat: 19,
      lng: 73,
      discount_percentage: 10
    },
    "tokyo": {
      lat: 35,
      lng: 139,
      discount_percentage: 15
    },
    "amsterdam": {
      lat: 52,
      lng: 5,
      discount_percentage: 20
    },
    "london": {
      lat: 51,
      lng: 0,
      discount_percentage: 25
    }
  }

  static calculateDiscount(price: number) {
    const location = JSON.parse(window.localStorage.getItem('GEO_LOCATION') || '{}');
    let discountPrice: number = 0;

    const cities = Object.keys(DiscountUtil.coordinates);
    cities.forEach((city: string) => {
      const coordinate = DiscountUtil.coordinates[city];
      const hasMatchingLat = location.lat >= coordinate.lat - 2 && location.lat <= coordinate.lat + 2;
      const hasMatchingLng = location.lng >= coordinate.lng - 2 && location.lng <= coordinate.lng + 2;
      if (hasMatchingLat && hasMatchingLng) {
        discountPrice = price - (price * (coordinate.discount_percentage / 100));
      }
    });
    return Math.round((discountPrice + Number.EPSILON) * 100) / 100;
  }

  static getDiscountPercentage() {
    const location = JSON.parse(window.localStorage.getItem('GEO_LOCATION') || '{}');
    let discountPercentage: number = 0;

    const cities = Object.keys(DiscountUtil.coordinates);
    cities.forEach((city: string) => {
      const coordinate = DiscountUtil.coordinates[city];
      const hasMatchingLat = location.lat >= coordinate.lat - 2 && location.lat <= coordinate.lat + 2;
      const hasMatchingLng = location.lng >= coordinate.lng - 2 && location.lng <= coordinate.lng + 2;
      if (hasMatchingLat && hasMatchingLng) {
        discountPercentage = coordinate.discount_percentage;
      }
    });
    return discountPercentage;
  }
}
