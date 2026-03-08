
### intervals interface and class hierarchy

- **DateOrTimeInterval** marking interface
- **DateOrTimeIntervalSet** marking interface


- **Interval<T>** interface ext. Equalable, Comparable, IntersectComparable
  - *IntInterval<int>* 
    - *NonNegativeIntInterval<int>* 
    - *PositiveIntInterval<int>*
  - *FloatInterval<float>* impl. OpenClosedInterval<T>
  - *DateInterval<Date>* impl. DateOrTimeInterval
    - *WeekDateInterval<Date>*
  - *NightInterval<Date>* impl. DateOrTimeInterval
  - *DateTimeInterval<DateTime>* impl. DateOrTimeInterval


- **IntervalSet<T>** interface ext. Equalable, IteratorAggregate
  - *IntIntervalSet<IntInterval>*
  - *FloatIntervalSet<FloatInterval>*
  - *DateIntervalSet<DateInterval>* impl. DateOrTimeIntervalSet
  - *NightIntervalSet<NightInterval>* impl. DateOrTimeIntervalSet
  - *DateTimeIntervalSet<DateTimeInterval>* impl. DateOrTimeIntervalSet


- **ModuloInterval<T>** interface ext. Equalable, Comparable
  - *TimeInterval<Time>* impl. DateOrTimeInterval
  - *DayOfYearInterval<DayOfYear>* (TODO: should impl. DateOrTimeInterval)


- **ModuloIntervalSet<T>** interface ext. Equalable, IteratorAggregate
  - *TimeIntervalSet<TimeInterval>* impl. DateOrTimeIntervalSet
  - *DayOfYearIntervalSet<DayOfYearInterval>* (TODO: should impl. DateOrTimeIntervalSet)


- *DateIntervalData*
- *DateIntervalDataSet*
- *NightIntervalData*
- *NightIntervalDataSet*
